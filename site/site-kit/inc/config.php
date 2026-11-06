<?php
/**
 * Typed access to config.php.
 *
 * Everything else in the plugin reads settings through this file, so there is
 * exactly one place that knows how configuration is stored and how missing
 * values fall back.
 */

namespace SiteKit\Config;

defined('ABSPATH') || exit;

/** @var array<string, mixed>|null Raw config, loaded once. */
$sk_config_cache = null;

/**
 * The whole configuration array: defaults.php with config.php merged on top.
 *
 * Merging is recursive, so a project only declares what it changes — e.g.
 * `['brand' => ['name' => 'Acme']]` keeps every other default intact.
 *
 * @return array<string, mixed>
 */
function all(): array
{
    global $sk_config_cache;

    if (is_array($sk_config_cache)) {
        return $sk_config_cache;
    }

    $defaults = [];
    $defaults_file = defined('SK_DIR') ? SK_DIR . 'inc/defaults.php' : '';
    if ($defaults_file && is_readable($defaults_file)) {
        $loaded   = require $defaults_file;
        $defaults = is_array($loaded) ? $loaded : [];
    }

    $file = defined('SK_DIR') ? SK_DIR . 'config.php' : '';
    $data = ($file && is_readable($file)) ? require $file : [];
    $data = is_array($data) ? $data : [];

    $merged = merge($defaults, $data);

    /**
     * Filter the whole configuration (tests, per-environment overrides).
     *
     * @param array<string, mixed> $merged
     */
    $sk_config_cache = (array) apply_filters('sk_config', $merged);

    return $sk_config_cache;
}

/**
 * Recursive array merge where list arrays are replaced, not concatenated.
 *
 * Associative arrays merge key by key, so `['brand' => ['name' => 'X']]`
 * keeps every other brand default. Lists (pages, nav, footer_nav, field
 * definitions) are replaced as a whole: array_replace_recursive() would merge
 * them index by index, so a config with two footer links would silently keep
 * the third and fourth default link. An empty array means "keep the default".
 *
 * @param array<string, mixed> $base
 * @param array<string, mixed> $override
 * @return array<string, mixed>
 */
function merge(array $base, array $override): array
{
    foreach ($override as $key => $value) {
        if ($value === [] && array_key_exists($key, $base)) {
            continue;
        }

        if (is_array($value) && !is_list($value)
            && isset($base[$key]) && is_array($base[$key]) && !is_list($base[$key])
        ) {
            $base[$key] = merge($base[$key], $value);
            continue;
        }

        $base[$key] = $value;
    }

    return $base;
}

/** array_is_list() for PHP 8.0: sequential integer keys starting at 0. */
function is_list(array $array): bool
{
    return $array === [] || array_keys($array) === range(0, count($array) - 1);
}

/**
 * Read a config value with dot notation: get('brand.name').
 *
 * @param string $path    Dot-separated path.
 * @param mixed  $default Returned when the key does not exist.
 * @return mixed
 */
function get(string $path, $default = null)
{
    $value = all();

    foreach (explode('.', $path) as $key) {
        if (!is_array($value) || !array_key_exists($key, $value)) {
            return $default;
        }
        $value = $value[$key];
    }

    return $value;
}

/** Brand name, never empty. */
function brand_name(): string
{
    $name = trim((string) get('brand.name', ''));
    return $name !== '' ? $name : (string) get_bloginfo('name');
}

/** Short brand name (mobile menu, footer bar). */
function brand_short(): string
{
    $short = trim((string) get('brand.short', ''));
    return $short !== '' ? $short : brand_name();
}

/** Tagline / site description. */
function tagline(): string
{
    $tagline = trim((string) get('brand.tagline', ''));
    if ($tagline !== '') {
        return $tagline;
    }
    $desc = (string) get_bloginfo('description');
    return $desc !== '' ? $desc : (string) get('brand.description', '');
}

/**
 * Contact details with sensible fallbacks to the WordPress admin e-mail.
 *
 * @return array<string, string>
 */
function contact(): array
{
    static $contact = null;

    if (is_array($contact)) {
        return $contact;
    }

    $defaults = [
        'street'        => '',
        'city'          => '',
        'country'       => '',
        'phone'         => '',
        'email'         => (string) get_option('admin_email'),
        'recipient'     => (string) get_option('admin_email'),
        'whatsapp'      => '',
        'whatsapp_text' => '',
        'map'           => '',
        'map_label'     => '',
    ];

    $contact = array_merge($defaults, array_map('strval', (array) get('contact', [])));

    // An empty recipient goes to the site admin, not to the public address:
    // during setup the public address is often still a placeholder.
    if ($contact['recipient'] === '') {
        $contact['recipient'] = (string) get_option('admin_email');
    }
    if ($contact['email'] === '') {
        $contact['email'] = (string) get_option('admin_email');
    }

    return $contact;
}

/**
 * Emergency line settings. `enabled` is only true when a phone number exists.
 *
 * @return array{enabled: bool, phone: string, label: string, note: string, eta: string}
 */
function emergency(): array
{
    $raw = (array) get('emergency', []);

    $emergency = [
        'enabled' => !empty($raw['enabled']),
        'phone'   => trim((string) ($raw['phone'] ?? '')),
        'label'   => trim((string) ($raw['label'] ?? '')),
        'note'    => trim((string) ($raw['note'] ?? '')),
        'eta'     => trim((string) ($raw['eta'] ?? '')),
    ];

    $emergency['enabled'] = $emergency['enabled'] && $emergency['phone'] !== '';
    if ($emergency['label'] === '') {
        $emergency['label'] = 'Notdienst';
    }

    return $emergency;
}

/**
 * Opening hours as [day => [open, close]] or [day => null] when closed.
 *
 * Days are keyed mo…su. Malformed entries count as closed.
 *
 * @return array{timezone: string, week: array<string, array{0: string, 1: string}|null>}
 */
function hours(): array
{
    $raw  = (array) get('hours.week', []);
    $week = [];

    foreach (['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'] as $day) {
        $value = trim((string) ($raw[$day] ?? ''));
        $week[$day] = preg_match('/^(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})$/', $value, $m) ? [$m[1], $m[2]] : null;
    }

    $timezone = trim((string) get('hours.timezone', 'Europe/Berlin'));

    return [
        'timezone' => $timezone !== '' ? $timezone : 'Europe/Berlin',
        'week'     => $week,
    ];
}

/**
 * URL of the page that uses a given template, e.g. page_url('contact').
 *
 * Templates link to each other by role instead of by slug, so renaming a
 * page in config.php never breaks a link.
 *
 * @param string               $template Template name without prefix ("contact").
 * @param array<string, string> $query    Query arguments to append.
 * @param string               $fragment Anchor without '#'.
 */
function page_url(string $template, array $query = [], string $fragment = ''): string
{
    $url = '';

    foreach ((array) get('pages', []) as $page) {
        if (is_array($page) && ($page['template'] ?? '') === $template && !empty($page['slug'])) {
            $url = resolve_location((string) $page['slug']);
            break;
        }
    }

    if ($url === '') {
        return '';
    }
    if ($query) {
        $url = add_query_arg(array_map('rawurlencode', $query), $url);
    }
    if ($fragment !== '') {
        $url .= '#' . rawurlencode($fragment);
    }

    return $url;
}

/**
 * Social links that actually have a URL, keyed by network.
 *
 * @return array<string, string>
 */
function socials(): array
{
    $out = [];
    foreach ((array) get('social', []) as $network => $url) {
        $url = trim((string) $url);
        if ($url !== '') {
            $out[sanitize_key((string) $network)] = $url;
        }
    }

    return $out;
}

/**
 * Header navigation: label + resolved URL.
 *
 * Slugs that point at a post type archive resolve to its archive link,
 * everything else to a page permalink. Entries that resolve to nothing are
 * dropped, so a fresh install without pages still renders a valid header.
 *
 * @return array<int, array{label: string, url: string, slug: string}>
 */
function nav(): array
{
    $items = [];

    foreach ((array) get('nav', []) as $item) {
        if (!is_array($item) || empty($item['slug'])) {
            continue;
        }
        $slug = trim((string) $item['slug']);
        $url  = resolve_location($slug);
        if ($url === '') {
            continue;
        }
        $items[] = [
            'label' => (string) ($item['label'] ?? $slug),
            'url'   => $url,
            'slug'  => $slug,
        ];
    }

    return $items;
}

/**
 * Resolve a slug to an archive or page URL.
 *
 * @param string $slug Page slug or post type name.
 * @return string Empty string when neither exists.
 */
function resolve_location(string $slug): string
{
    if (post_type_exists($slug) && get_post_type_object($slug)->has_archive) {
        $archive = get_post_type_archive_link($slug);
        if (is_string($archive) && $archive !== '') {
            return $archive;
        }
    }

    $page = get_page_by_path($slug);
    if ($page instanceof \WP_Post && $page->post_status === 'publish') {
        return (string) get_permalink($page);
    }

    // Fall back to the archive even when the page is missing.
    if (post_type_exists($slug) && get_post_type_object($slug)->has_archive) {
        $archive = get_post_type_archive_link($slug);
        if (is_string($archive) && $archive !== '') {
            return $archive;
        }
    }

    return '';
}

/**
 * Updater settings, with wp-config.php constants taking precedence so a single
 * repository can be deployed to several hosts without code changes.
 *
 * @return array{enabled: bool, repo: string, branch: string, token: string, configured: bool}
 */
function updater(): array
{
    $repo   = trim((string) get('updater.repo', ''));
    $branch = trim((string) get('updater.branch', 'main'));

    // A host can point the same code at a fork/branch without editing the repo.
    if (defined('SK_GITHUB_REPO') && SK_GITHUB_REPO) {
        $repo = (string) SK_GITHUB_REPO;
    }
    if (defined('SK_GITHUB_BRANCH') && SK_GITHUB_BRANCH) {
        $branch = (string) SK_GITHUB_BRANCH;
    }

    $constant = (string) get('updater.token_constant', 'SK_GITHUB_TOKEN');
    $env      = (string) get('updater.token_env', $constant);

    $token = '';
    if ($constant !== '' && defined($constant) && constant($constant)) {
        $token = (string) constant($constant);
    } elseif ($env !== '' && getenv($env)) {
        $token = (string) getenv($env);
    }

    return [
        'enabled'    => (bool) get('updater.enabled', true),
        'repo'       => $repo,
        'branch'     => $branch !== '' ? $branch : 'main',
        'token'      => $token,
        'configured' => is_valid_repo($repo),
    ];
}

/** True when the repo string looks like "owner/name" and not a placeholder. */
function is_valid_repo(string $repo): bool
{
    if ($repo === '' || strpos($repo, 'your-github-user') === 0) {
        return false;
    }

    return (bool) preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo);
}

/** Whether a feature flag is on. */
function feature(string $name, bool $default = false): bool
{
    return (bool) get('features.' . $name, $default);
}

/** Version from the plugin header — the canonical version of the kit. */
function version(): string
{
    if (!function_exists('get_file_data') || !defined('SK_FILE')) {
        return '0.0.0';
    }

    $data = get_file_data(SK_FILE, ['Version' => 'Version']);

    return !empty($data['Version']) ? (string) $data['Version'] : '0.0.0';
}
