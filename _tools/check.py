#!/usr/bin/env python3
"""Statische Qualitätsprüfung für das Site-Kit-Template (ohne WordPress).

Prüft in dieser Reihenfolge:
  1. PHP-Syntax aller Projektdateien (Tag-Balance, Klammer-Balance,
     String-Ticks — bewusst ohne PHP-Binary als Basischeck)
  2. HTML-Tags in den Templates (div/section/... balanciert)
  3. Alle CSS-Klassen in den Templates existieren im Stylesheet
     (sk-... Namenskonvention)
  4. Alle in PHP referenzierten Assets (assets/img/...) existieren

Usage: python3 _tools/check.py [root]
Verlässt sich auf nichts außer der Python-Standardbibliothek; das Serialisieren
der Sprache PHP ist hier bewusst heuristisch, um in jedem CI ohne Abhängigkeiten
zu laufen.
"""

import os
import re
import sys
from collections import defaultdict

ROOT = os.path.abspath(sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), '..'))
THEME = os.path.join(ROOT, '')
PLUGIN = os.path.join(ROOT, 'site', 'site-kit')
CSS_FILE = os.path.join(PLUGIN, 'assets', 'css', 'main.css')

issues = []


def add(path, message):
    issues.append(f"{os.path.relpath(path, ROOT)}: {message}")


# --------------------------------------------------------------------------
# 1) PHP syntax heuristic
# --------------------------------------------------------------------------

def check_php(path):
    src = open(path, encoding='utf-8').read()
    errors = []

    # Extract PHP code blocks
    in_php = False
    blocks = []
    for m in re.finditer(r'<\?(php|=)?|\?>', src):
        tok = m.group(0)
        if tok.startswith('<?') and not in_php:
            blocks.append([m.end(), None])
            in_php = True
        elif tok == '?>' and in_php:
            blocks[-1][1] = m.start()
            in_php = False
    if in_php:
        blocks[-1][1] = len(src)

    parts = [src[s:e if e is not None else len(src)] for s, e in blocks]
    code = "\n".join(parts)

    # Strip strings and comments
    out = []
    i = 0
    n = len(code)
    mode = None
    while i < n:
        ch = code[i]
        if mode is None:
            if ch == "'":
                mode = 'sq'
            elif ch == '"':
                mode = 'dq'
            elif ch == '/' and i + 1 < n and code[i + 1] == '/':
                mode = 'line'
            elif ch == '#' and code[i:i + 3] in ('##', '#[', '# '):
                mode = 'line'
            elif ch == '#' and i + 1 < n and code[i + 1] == '[' and code[i:i + 2] != '#[':
                mode = 'line'
            elif ch == '/' and i + 1 < n and code[i + 1] == '*':
                mode = 'block'
                i += 1
            else:
                out.append(ch)
        elif mode == 'sq':
            if ch == '\\':
                i += 1
            elif ch == "'":
                mode = None
        elif mode == 'dq':
            if ch == '\\':
                i += 1
            elif ch == '"':
                mode = None
        elif mode == 'line':
            if ch == '\n':
                mode = None
                out.append(ch)
        elif mode == 'block':
            if ch == '*' and i + 1 < n and code[i + 1] == '/':
                mode = None
                i += 1
        i += 1

    clean = ''.join(out)

    # Bracket balance
    stack = []
    pairs = {'(': ')', '[': ']', '{': '}'}
    rev = {v: k for k, v in pairs.items()}
    for i2, ch in enumerate(clean):
        if ch in pairs:
            stack.append(ch)
        elif ch in rev:
            if not stack or stack[-1] != rev[ch]:
                errors.append(f"bracket mismatch at offset {i2}: unexpected '{ch}'")
                break
            stack.pop()
    if stack:
        errors.append(f"unclosed brackets: {stack[-4:]}")

    # Attribute/shortcode sanity: non-empty PHP files must start with <?php
    if src.lstrip()[:5] not in ('<?php', '<?'):
        errors.append("file does not start with a PHP open tag")

    return errors


# --------------------------------------------------------------------------
# 2) Comment structure: catch stray "*" lines left in raw PHP code
#
# This is the failure mode of a half-rewritten docblock: a "/* ... */" block
# is closed too early and the remaining lines (" * ----- */") survive as code.
# PHP only fails at parse time, so flag it statically.
# --------------------------------------------------------------------------

def check_comment_structure(path):
    src = open(path, encoding='utf-8').read()
    errors = []

    # Character mask: 1 = inside a comment or string literal.
    masked = bytearray(len(src))
    in_php = False
    mode = None      # None | '"' | "'" | 'line' | 'block'
    open_at = None
    i, n = 0, len(src)

    while i < n:
        if not in_php:
            if src.startswith('<?php', i):
                in_php, i = True, i + 5
                continue
            if src.startswith('<?=', i):
                in_php, i = True, i + 3
                continue
            i += 1
            continue
        if mode is None:
            if src.startswith('?>', i):
                in_php, i = False, i + 2
                continue
            ch = src[i]
            if ch in ('"', "'"):
                mode = ch
            elif src.startswith('/*', i):
                mode, open_at, i = 'block', i, i + 2
                continue
            elif src.startswith('//', i) or ch == '#':
                mode = 'line'
            i += 1
            continue
        if mode in ('"', "'"):
            masked[i] = 1
            if src[i] == '\\':
                i += 2          # escaped char inside the literal
                continue
            if src[i] == mode:
                mode = None
            i += 1
            continue
        if mode == 'line':
            if src[i] == '\n':
                mode = None
            else:
                masked[i] = 1
            i += 1
            continue
        # block comment
        if src.startswith('*/', i):
            masked[i] = masked[i + 1] = 1
            mode = None
            i += 2
            continue
        masked[i] = 1
        i += 1

    if mode == 'block':
        line = src.count('\n', 0, open_at) + 1
        errors.append(f"unclosed block comment opened at line {line}")

    # Any line whose first non-space character is a bare "*" outside a comment
    # is a docblock that was closed too early.
    offset = 0
    for lineno, line in enumerate(src.splitlines(True), start=1):
        stripped = line.lstrip()
        if stripped.startswith('*'):
            lead = offset + (len(line) - len(stripped))
            if lead < len(masked) and masked[lead] == 0:
                errors.append(
                    f"line {lineno}: stray '*' outside a comment "
                    "(docblock closed too early?)"
                )
        offset += len(line)

    return errors


HTML_TAGS = ('div', 'section', 'article', 'form', 'nav', 'header', 'footer',
             'main', 'ul', 'ol', 'table', 'figure', 'details', 'aside', 'a',
             'button', 'select', 'span', 'h1', 'h2', 'h3', 'h4', 'p', 'li', 'blockquote')

VOID_TAGS = ('input', 'img', 'br', 'hr', 'meta', 'link', 'source', 'path', 'circle', 'rect', 'ellipse', 'line')


def strip_php(src):
    return re.sub(r'<\?php.*?(?:\?>|$)', '', src, flags=re.S)


def remove_void_tags(html):
    for tag in VOID_TAGS:
        html = re.sub(rf'<{tag}\b[^>]*/?>', '', html)
    return html


def check_html(path):
    src = strip_php(open(path, encoding='utf-8').read())
    src = re.sub(r'<script.*?</script>', '', src, flags=re.S)
    src = re.sub(r'<!--.*?-->', '', src, flags=re.S)
    src = remove_void_tags(src)

    errors = []
    for tag in HTML_TAGS:
        opening = len(re.findall(rf'<{tag}\b[^>]*(?<!/)>', src))
        closing = len(re.findall(rf'</{tag}>', src))
        if opening != closing:
            errors.append(f"tag <{tag}> unbalanced: {opening} opened, {closing} closed")
    return errors


# --------------------------------------------------------------------------
# 3+4) Collect classes and asset references from PHP templates/partials
# --------------------------------------------------------------------------

def css_classes_defined(css_file):
    css = open(css_file, encoding='utf-8').read()
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    defined = set(re.findall(r'\.((?:sk|is|no-js)-[a-zA-Z0-9_-]+)', css))
    # Classes generated with modifiers also count as defined
    return defined


def classes_in_markup(html):
    """All class="..." values, supporting PHP interpolation inside."""
    found = []
    for match in re.findall(r'class="([^"]*)"', html):
        # split on spaces, keep literal PHP-ish tokens
        for token in re.split(r'\s+', match):
            token = token.strip()
            if token and not token.startswith('<?') and not token.startswith('$'):
                # literal → check; conditional PHP fragments produce class lists like is-active / is-open
                if re.fullmatch(r'[a-zA-Z0-9_-]+', token):
                    found.append(token)
    return found


def php_string_values(src):
    """Extract string literals (single-quoted) for asset references."""
    return re.findall(r"'([^']*)'", src) + re.findall(r'"([^"]*)"', src)


# THEME is the repository root, so this walk also covers style.css's
# neighbours: functions.php, index.php and wp-config-example.php.
PHP_FILES = []
_seen = set()
for base in (THEME, PLUGIN):
    for dirpath, dirnames, filenames in os.walk(base):
        if 'plugin-update-checker' in dirpath:  # vendored, skip
            dirnames[:] = []
            continue
        dirnames[:] = [d for d in dirnames if d != '.git']
        for f in filenames:
            if not f.endswith('.php'):
                continue
            full = os.path.join(dirpath, f)
            if full in _seen:
                continue
            _seen.add(full)
            PHP_FILES.append(full)
PHP_FILES.sort()

print(f"Checking {len(PHP_FILES)} PHP files…")

for path in PHP_FILES:
    for err in check_php(path):
        add(path, err)
    for err in check_comment_structure(path):
        add(path, err)
    if '/templates/' in path.replace(os.sep, '/'):
        for err in check_html(path):
            add(path, err)

# CSS class consistency
defined = css_classes_defined(CSS_FILE)
used = defaultdict(set)
for path in PHP_FILES:
    if '/templates/' not in path:
        continue
    src = open(path, encoding='utf-8').read()
    for cls in classes_in_markup(strip_php(src)) + re.findall(r"class=\s*'([a-z0-9 -]+)'", src):
        for token in cls.split():
            used[token].add(os.path.basename(path))

sk_used = {c: files for c, files in used.items() if c.startswith('sk-')}
# Marker classes that deliberately carry no own rules: they combine with
# component classes (.sk-card.sk-news) or are generated by CSS custom
# properties / component modifiers whose rules live elsewhere.
MARKERS = {
    'sk-event', 'sk-news', 'sk-person',  # component variants styled via .sk-*__
    'sk-hero__copy',                      # grid child; layout via .sk-hero__grid
    'sk-docs__main',                      # grid child of .sk-docs__item
    'sk-notice--',                        # false positive: PHP interpolates 'ok'/'error'
}
missing = {c: files for c, files in sk_used.items()
           if c not in defined and c not in MARKERS}
if missing:
    for cls, files in sorted(missing.items()):
        add(CSS_FILE, f"class .{cls} used in {', '.join(sorted(files))} but not defined in CSS")

# Asset references
for path in PHP_FILES:
    if '/templates/' not in path and 'inc/util.php' not in path:
        continue
    src = open(path, encoding='utf-8').read()
    for ref in php_string_values(src):
        # Normalise the reference styles the code uses:
        #   'img/site/x.svg'      (arguments to Util\asset / Util\img)
        #   'site/x.svg'           (arguments to Util\img)
        #   'assets/img/site/x.png'(raw config values)
        target = None
        if ref.startswith('img/'):
            target = os.path.join(PLUGIN, 'assets', ref)
        elif ref.startswith('site/') and re.fullmatch(r'site/[a-z0-9-]+\.(svg|png|jpg|jpeg|webp|woff2)', ref):
            target = os.path.join(PLUGIN, 'assets', 'img', ref)
        elif ref.startswith('assets/img/'):
            target = os.path.join(PLUGIN, 'assets', ref[len('assets/'):])
        if target and not os.path.isfile(target):
            add(path, f"referenced asset missing: {ref}")

# Fonts must be next to the stylesheet
if not os.path.isfile(os.path.join(PLUGIN, 'assets', 'fonts', 'archivo-var-latin.woff2')):
    add(os.path.join(PLUGIN, 'assets', 'fonts'), "Archivo variable font missing")

# --------------------------------------------------------------------------
# 5) Theme header: style.css must be plain CSS with a parseable header
#
# WordPress reads style.css as text and parses "Key: value" lines inside the
# FIRST comment. A stray "<?php" or a missing header silently makes the theme
# uninstallable, so both are checked here.
# --------------------------------------------------------------------------

STYLE_CSS = os.path.join(ROOT, 'style.css')
REQUIRED_HEADER = ('Theme Name', 'Version', 'Requires PHP', 'Text Domain')

if not os.path.isfile(STYLE_CSS):
    add(STYLE_CSS, "style.css missing (WordPress needs it to recognise the theme)")
else:
    css_src = open(STYLE_CSS, encoding='utf-8').read()
    head = css_src.lstrip()
    if head.startswith('<?'):
        add(STYLE_CSS, "starts with a PHP open tag — style.css must be plain CSS")
    if not head.startswith('/*'):
        add(STYLE_CSS, "does not start with a /* theme header comment */")
    else:
        close = css_src.find('*/')
        header = css_src[:close] if close != -1 else ''
        if close == -1:
            add(STYLE_CSS, "theme header comment is not closed with */")
        for key in REQUIRED_HEADER:
            if not re.search(rf'^\s*(?:\*\s*)?{re.escape(key)}\s*:', header, re.M):
                add(STYLE_CSS, f"theme header is missing '{key}:'")
        if 'Version' in header and not re.search(r'^\s*(?:\*\s*)?Version\s*:\s*\S+', header, re.M):
            add(STYLE_CSS, "theme header has an empty Version")

print()
if issues:
    print(f"{len(issues)} issue(s):")
    for message in sorted(set(issues)):
        print(f"  {message}")
    sys.exit(1)
print("All checks passed: PHP syntax + comments, theme header, HTML balance, "
      "CSS classes, asset references.")
