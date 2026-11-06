# WordPress Site Kit — a reusable, GitHub-deployed website template

A stripped-down, reusable base for WordPress sites that are **developed in a
Git repository and deployed by pushing**. It contains **zero project-specific
content**: names, texts, post types and pages are all configuration.

```
┌────────────────────────── Repository root = WordPress theme ──────────────────────────┐
│  style.css        valid, empty theme header                                              │
│  functions.php    carrier: GitHub updater + mu-plugin sync                               │
│  index.php        fallback no-op template                                                │
│  inc/…            plugin-update-checker 5.7 (vendored, MIT)                              │
│                                                                                          │
│  site/             → synced into wp-content/mu-plugins/ on install and every update     │
│  ├─ sk-loader.php   mu-plugin loader (WP ignores sub-directories in mu-plugins/)        │
│  └─ site-kit/       The entire website                                                   │
│     ├─ config.php   ★ the one file you edit per project                                 │
│     ├─ site-kit.php plugin bootstrap                                                     │
│     ├─ inc/         config, CPT registry, seeder, forms, template resolver…              │
│     ├─ templates/   all page templates + partials                                        │
│     └─ assets/     CSS design system, JS, fonts, placeholders                            │
└──────────────────────────────────────────────────────────────────────────────────────────┘
```

## Why this layout

- **Push to deploy.** The repository is a valid theme. Push to `main`, and
  WordPress shows an update; installing it re-syncs `site/` into
  `wp-content/mu-plugins/`. No FTP, no plugin-install clicks.
- **Theme-independent.** The mu-plugin provides every template through
  `template_include`, so a broken or switched theme can never take the site
  down — the carrier theme only installs and updates the plugin.
- **One config file.** Brand, contact, navigation, post types, meta fields and
  pages come from `site/site-kit/config.php`, merged over the defaults in
  `inc/defaults.php`. Fork → edit one file → done.
- **Content stays in WordPress.** Editors write pages, events and news in the
  normal admin. Code owns layout; the database owns content.

## Starting a new project

You need: a fresh WordPress 6.x install (PHP ≥ 8.0) with an empty database,
an empty GitHub repository, and SSH access to a host profile that lets you edit
`wp-config.php`.

1. **Copy this template** into the new repository and make it your own:

   ```bash
   git clone <this-repo> my-new-site && cd my-new-site
   rm -rf .git && git init && git checkout -b main
   ```

2. **Edit `site/site-kit/config.php`** — it is the project file:

   | Key                       | What it drives                                        |
   |---------------------------|-------------------------------------------------------|
   | `updater.repo`            | `owner/name` of the new GitHub repository             |
   | `updater.branch`          | Branch updates come from (usually `main`)            |
   | `updater.token_constant`  | Name of the wp-config constant holding the token      |
   | `brand.*`                 | Names, tagline, description, colours                 |
   | `contact.*`              | Address, e-mail, recipient for forms, optional map    |
   | `social.*`               | Social profile URLs (empty ones disappear)           |
   | `nav` / `cta`             | Header navigation and the highlighted button         |
   | `features.*`              | Demo content, RSVP, search toggles                   |
   | `content` / `pages`       | Post types, meta fields, seeded pages (see below)     |

3. **Create a fine-grained personal access token** on GitHub for the new
   repository needs: *Repository access → only repositories → your repo*, then
   *Permissions → Contents → Read*.

4. **Install on the host** — upload the repository ZIP via
   *Design → Themes → Add → Upload*, activate it. The theme immediately copies
   `site/` into `wp-content/mu-plugins/` and the seeder creates all pages,
   the navigation and demo content on the first admin visit.

5. **Add the token to the host's `wp-config.php`**, above the
   `/* That's all, stop editing! */` line:

   ```php
   define( 'SK_GITHUB_TOKEN', 'github_pat_…' );
   // Optional per-host overrides:
   // define( 'SK_GITHUB_REPO', 'owner/override-repo' );   // deploy a fork
   // define( 'SK_GITHUB_BRANCH', 'staging' );              // deploy a branch
   ```

   The repository **stays private-able**: tokens live only on the host.

6. **Check the Tools → Site Kit page.** It shows the seed status, whether all
   pages exist with their templates, the permalink state and whether the
   updater found its repository and token, with one-click repairs.

## Deployment flow

```
edit → commit → push to main
                 └→ WordPress periodically checks GitHub (plugin-update-checker)
                    → themes page shows "Update available"
                    → update replaces the theme…
                       …and re-syncs site/ into wp-content/mu-plugins/
```

Bump the `Version:` header in `style.css` (and the constant in
`site/site-kit/site-kit.php`) in the same commit that changes something — the
version decides when WordPress offers the update and when the seeder re-runs
its repair pass.

## The content model is config, not code

`inc/defaults.php` ships events, news, documents and people with their meta
fields. All of it is overridable in `config.php`:

```php
'content' => [
    'post_types' => [
        'event' => [
            'labels' => ['name' => 'Termine'],   // override just labels…
        ],
        'course' => [                            // …or add a whole new type
            'labels'     => ['name' => 'Courses', 'singular_name' => 'Course'],
            'icon'       => 'dashicons-welcome-learn-view',
            'supports'   => ['title', 'editor', 'thumbnail'],
            'has_archive'=> true,
            'rewrite'    => 'courses',
            'taxonomies' => ['course_topic'],
        ],
    ],
    'taxonomies' => [
        'course_topic' => [
            'labels'     => ['name' => 'Topics'],
            'post_types' => ['course'],
            'rewrite'    => 'topic',
        ],
    ],
    'meta' => [
        'course' => [
            ['key' => 'sk_course_level', 'label' => 'Level', 'type' => 'select',
             'box' => 'Course details', 'options' => ['beginner' => 'Beginner', 'advanced' => 'Advanced']],
        ],
    ],
],
```

Registration, admin columns, meta boxes, sanitising and REST exposure are all
generated from these arrays. Archives and single views fall back to
`template-archive.php` / `template-single.php`, so a new post type is visible
without writing a template. Dedicated templates follow a naming convention:
`template-<type>.php` for the archive, `template-single-<type>.php` for single
views.

## Forms

The contact form and event RSVP run through `admin-post.php` with layered,
dependency-free spam protection: nonce, honeypot, submit-time window and a
per-IP rate limit (8/10 min). Submissions go to `wp_mail()` only — **no form
data is stored in the database**. The recipient is `contact.recipient` in
config.php. Use an SMTP plugin on the host for reliable delivery.

## Design system

`assets/css/main.css` is a complete, unframeworked design system: tokens,
header with auto-hide, full-screen mobile nav, hero, page hero, cards, agenda,
tabs/filters, documents list, forms, notices, footers, print styles. All state
progressive: scroll reveals and count-ups disable under
`prefers-reduced-motion`, the layout works from 320 px, interactive elements
keep visible focus rings.

Rebranding happens in `config.php` (`brand.colors`) — the five colour tokens
are injected as CSS variables at runtime, so most projects never touch CSS.

## Quality gate

```bash
python3 _tools/check.py .        # PHP syntax + comments, theme header, markup, CSS, assets
php _tools/smoke_config.php      # config merge + resolver fallbacks (stubbed WP)
php _tools/smoke_theme.php       # updater bootstrap, template/partials wiring
```

`check.py` runs anywhere (pure Python, no PHP needed) and is the gate you can
always run. It catches the two failure modes that otherwise only show up in a
browser: a `style.css` without a valid theme header (WordPress then refuses to
recognise the theme) and PHP comment blocks that were closed too early, which
leave `*` lines behind as syntax errors.

The two smoke scripts need a PHP ≥ 8.0 binary but not WordPress. All three exit
non-zero on failure and are safe for CI.

## What was deliberately removed from the source project

This template was derived from a production association website. Removed to
keep the base reusable: all texts, people, events, motions and statistics; the
static `preview/` folder and `make_preview.py` (the design is now either
visible on the seeded install or not worth shipping twice); the two multi-step
membership forms with SEPA/IBAN validation (project-specific compliance
territory — copy them back from the source project if you build something
similar); the `_wp_page_template` per-page template picker conflict fixes (the
kit assigns templates at seed time instead).

## License

GPL-2.0-or-later for the kit; `inc/plugin-update-checker` is MIT
(Yahnis Elsts); Archivo is OFL. Placeholder images are free to replace.
