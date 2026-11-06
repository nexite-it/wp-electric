<?php
/**
 * Output helpers shared by the templates.
 *
 * Keeping these in one place means templates stay readable and asset paths,
 * date formats and escaping are only written once.
 */

namespace SiteKit\Util;

defined('ABSPATH') || exit;

/**
 * URL of a file inside the plugin's assets/ directory.
 *
 * @param string $rel Path relative to assets/, e.g. "css/main.css".
 */
function asset(string $rel): string
{
    return plugins_url('assets/' . ltrim($rel, '/'), SK_FILE);
}

/**
 * URL of a bundled image, with a graceful fallback to the placeholder.
 *
 * @param string $rel Path relative to assets/img/.
 */
function img(string $rel): string
{
    $rel = ltrim($rel, '/');
    $abs = SK_DIR . 'assets/img/' . $rel;

    if (!is_readable($abs)) {
        $rel = 'site/placeholder.svg';
    }

    return asset('img/' . $rel);
}

/**
 * Cache-busting version for an asset: file mtime, else the plugin version.
 *
 * @param string $rel Path relative to assets/.
 */
function asset_version(string $rel): string
{
    $abs = SK_DIR . 'assets/' . ltrim($rel, '/');

    return is_readable($abs) ? (string) filemtime($abs) : SK_VERSION;
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name  Icon name from the set in inc/icons.php.
 * @param string $class CSS class ('' for none).
 */
function icon(string $name, string $class = 'sk-icon'): void
{
    echo \SiteKit\Icons\get($name, $class); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup is built internally
}

/** Inline SVG icon as a string. */
function icon_html(string $name, string $class = 'sk-icon'): string
{
    return \SiteKit\Icons\get($name, $class);
}

/**
 * Human date for a post, honouring the site locale.
 *
 * @param int|\WP_Post|null $post    Post or ID (null = current post).
 * @param string            $format  PHP date format.
 */
function date($post = null, string $format = 'j. F Y'): string
{
    $post = get_post($post);
    if (!$post instanceof \WP_Post) {
        return '';
    }

    return (string) date_i18n($format, strtotime($post->post_date));
}

/**
 * ISO date for <time datetime="...">.
 *
 * @param int|\WP_Post|null $post
 */
function date_iso($post = null): string
{
    $post = get_post($post);

    return $post instanceof \WP_Post ? (string) get_the_date('c', $post) : '';
}

/**
 * Excerpt of a post, trimmed to a word count.
 *
 * @param int|\WP_Post|null $post
 */
function excerpt($post = null, int $words = 20): string
{
    $post = get_post($post);
    if (!$post instanceof \WP_Post) {
        return '';
    }

    $text = has_excerpt($post)
        ? $post->post_excerpt
        : strip_shortcodes((string) $post->post_content);
    $text = wp_strip_all_tags($text);

    return wp_trim_words($text, $words, '…');
}

/**
 * A configured meta value for a post, with a typed fallback.
 *
 * @param int|\WP_Post|null $post
 */
function meta(string $key, $post = null, string $default = ''): string
{
    $post = get_post($post);
    if (!$post instanceof \WP_Post) {
        return $default;
    }

    $value = get_post_meta($post->ID, $key, true);

    return is_scalar($value) && (string) $value !== '' ? (string) $value : $default;
}

/**
 * Featured image URL, or a bundled placeholder.
 *
 * @param int|\WP_Post|null $post
 * @param string            $size
 * @param string            $placeholder Path relative to assets/img/.
 */
function thumb($post = null, string $size = 'medium_large', string $placeholder = 'site/placeholder.svg'): string
{
    $post = get_post($post);
    $url  = $post instanceof \WP_Post ? get_the_post_thumbnail_url($post, $size) : '';

    return is_string($url) && $url !== '' ? $url : img($placeholder);
}

/**
 * Published posts of a type, newest first.
 *
 * @return array<int, \WP_Post>
 */
function posts(string $type = 'post', int $limit = 6, array $extra = []): array
{
    $args = array_merge([
        'post_type'           => $type,
        'post_status'         => 'publish',
        'posts_per_page'      => $limit,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ], $extra);

    return get_posts($args);
}

/**
 * Upcoming events (meta-date based), falling back to the most recent past
 * events so the section is never empty on a fresh install.
 *
 * @return array{items: array<int, \WP_Post>, upcoming: bool}
 */
function upcoming_events(int $limit = 4): array
{
    if (!post_type_exists('event')) {
        return ['items' => [], 'upcoming' => false];
    }

    $today  = current_time('Y-m-d');
    $common = [
        'post_type'      => 'event',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_key'       => 'sk_event_date',
        'orderby'        => 'meta_value',
        'no_found_rows'  => true,
    ];

    $items = get_posts($common + [
        'order'      => 'ASC',
        'meta_query' => [[
            'key'     => 'sk_event_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ]],
    ]);

    if ($items) {
        return ['items' => $items, 'upcoming' => true];
    }

    $items = get_posts($common + ['order' => 'DESC']);

    return ['items' => $items, 'upcoming' => false];
}

/**
 * Split a date string into day/month/year parts for date badges.
 *
 * @return array{day: string, month: string, year: string}
 */
function date_parts(string $ymd): array
{
    $ts = $ymd !== '' ? strtotime($ymd) : false;

    if ($ts === false) {
        return ['day' => '', 'month' => '', 'year' => ''];
    }

    return [
        'day'   => date_i18n('d', $ts),
        'month' => date_i18n('M', $ts),
        'year'  => date_i18n('Y', $ts),
    ];
}

/** Long, localized date ("Friday, 4 April 2026"). */
function long_date(string $ymd): string
{
    $ts = $ymd !== '' ? strtotime($ymd) : false;

    return $ts !== false ? (string) date_i18n('l, j. F Y', $ts) : '';
}

/** True when an ISO date is today or later. */
function is_future(string $ymd): bool
{
    return $ymd !== '' && $ymd >= current_time('Y-m-d');
}

/**
 * Paginated archive links for the current main query.
 *
 * Uses the theme-independent `paginate_links()` markup so it works with any
 * permalink structure.
 */
function pagination(): string
{
    $links = paginate_links([
        'type'      => 'array',
        'mid_size'  => 1,
        'prev_text' => '&larr;',
        'next_text' => '&rarr;',
    ]);

    if (!is_array($links) || count($links) < 2) {
        return '';
    }

    $out = '<nav class="sk-pagination" aria-label="' . esc_attr__('Pagination', 'site-kit') . '">';
    foreach ($links as $link) {
        $out .= '<span class="sk-pagination__item">' . $link . '</span>';
    }
    $out .= '</nav>';

    return $out;
}

/**
 * Standard page hero (title band) used by every inner page.
 *
 * @param array{title?: string, lead?: string, crumbs?: array<int, array{label: string, url?: string}>, eyebrow?: string, meta?: string} $args
 */
function page_hero(array $args = []): void
{
    \SiteKit\Template\partial('page-hero', $args);
}

/**
 * Section heading row: eyebrow, title and an optional action link.
 *
 * @param array{eyebrow?: string, title?: string, side?: string, action?: array{label: string, url: string}, level?: string} $args
 */
function head_row(array $args = []): void
{
    \SiteKit\Template\partial('head-row', $args);
}

/**
 * Render a form status notice from the ?sk_form=/?sk_msg= query arguments.
 *
 * @param array<string, string> $messages Map of message code => text.
 * @return array{0: bool, 1: string} [ok, text]
 */
function form_notice(array $messages): array
{
    if (!isset($_GET['sk_form'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return [false, ''];
    }

    $ok   = sanitize_key(wp_unslash((string) $_GET['sk_form'])) === 'ok'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $code = isset($_GET['sk_msg']) ? sanitize_key(wp_unslash((string) $_GET['sk_msg'])) : 'generic'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    return [$ok, $messages[$code] ?? ($messages['generic'] ?? '')];
}

/** Echo a notice box. */
function notice(bool $ok, string $text): void
{
    if ($text === '') {
        return;
    }
    \SiteKit\Template\partial('notice', ['ok' => $ok, 'text' => $text]);
}

/**
 * Hidden anti-spam fields shared by every form.
 *
 * @param string $action Nonce action name.
 */
function form_security_fields(string $action): void
{
    printf(
        '<input type="hidden" name="sk_nonce" value="%s">%s<input type="hidden" name="sk_t" value="%d">%s',
        esc_attr(wp_create_nonce($action)),
        "\n",
        (int) time(),
        "\n"
    );
    echo '<div class="sk-hp" aria-hidden="true"><label>' . esc_html__('Leave this field empty', 'site-kit')
        . '<input type="text" name="sk_hp" tabindex="-1" autocomplete="off"></label></div>' . "\n";
}

/**
 * Anchor for a form on the current page, so redirects land on the right spot.
 *
 * @param string $anchor Anchor id without '#'.
 */
function form_action_url(string $anchor = ''): string
{
    $url = admin_url('admin-post.php');
    if ($anchor !== '') {
        $url .= '#' . rawurlencode($anchor);
    }

    return $url;
}

/* -------------------------------------------------------------------------
 * Local business helpers: phone links, WhatsApp, opening hours
 * ---------------------------------------------------------------------- */

/**
 * tel: URL for a German phone number in any notation.
 *
 * "0228 123 456-0" → "tel:+492281234560". International numbers ("+49…",
 * "0049…") are kept; a national number gets the German country code so the
 * link also works from foreign SIM cards.
 */
function tel(string $number): string
{
    $digits = preg_replace('/[^\d+]/', '', $number) ?? '';

    if (strpos($digits, '00') === 0) {
        $digits = '+' . substr($digits, 2);
    } elseif (strpos($digits, '0') === 0) {
        $digits = '+49' . substr($digits, 1);
    }

    return $digits !== '' ? 'tel:' . $digits : '';
}

/**
 * wa.me link for the configured WhatsApp number, or '' when none is set.
 *
 * @param string $text Prefilled message; defaults to contact.whatsapp_text.
 */
function whatsapp_url(string $text = ''): string
{
    $contact = \SiteKit\Config\contact();
    $tel     = tel($contact['whatsapp']);

    if ($tel === '') {
        return '';
    }

    $text = $text !== '' ? $text : $contact['whatsapp_text'];
    $url  = 'https://wa.me/' . ltrim(substr($tel, 4), '+');

    return $text !== '' ? $url . '?text=' . rawurlencode($text) : $url;
}

/** German short weekday labels, keyed like config.php → hours.week. */
const DAYS = ['mo' => 'Mo', 'tu' => 'Di', 'we' => 'Mi', 'th' => 'Do', 'fr' => 'Fr', 'sa' => 'Sa', 'su' => 'So'];

/**
 * Opening hours grouped for display: consecutive days with equal hours share
 * one row ("Mo – Do 07:00 – 17:00 Uhr").
 *
 * @return array<int, array{days: string, time: string, closed: bool}>
 */
function hours_rows(): array
{
    $week = \SiteKit\Config\hours()['week'];
    $rows = [];
    $keys = array_keys(DAYS);

    $i = 0;
    while ($i < count($keys)) {
        $slot  = $week[$keys[$i]];
        $start = $i;
        while ($i + 1 < count($keys) && $week[$keys[$i + 1]] === $slot) {
            $i++;
        }

        $first = DAYS[$keys[$start]];
        $last  = DAYS[$keys[$i]];
        $days  = $start === $i ? $first : $first . ($i - $start === 1 ? ', ' : ' – ') . $last;

        $rows[] = [
            'days'   => $days,
            'time'   => $slot ? $slot[0] . ' – ' . $slot[1] . ' Uhr' : __('geschlossen', 'site-kit'),
            'closed' => $slot === null,
        ];
        $i++;
    }

    // A week without any opening hours is "not configured", not "closed".
    $open = array_filter($rows, static fn(array $row): bool => !$row['closed']);

    return $open ? $rows : [];
}

/** One-line summary of the open days ("Mo – Do 07:00 – 17:00 Uhr · Fr …"). */
function hours_summary(): string
{
    $parts = [];
    foreach (hours_rows() as $row) {
        if (!$row['closed']) {
            $parts[] = $row['days'] . ' ' . $row['time'];
        }
    }

    return implode(' · ', $parts);
}

/** Opening hours as JSON for the live "jetzt geöffnet" indicator in main.js. */
function hours_json(): string
{
    $hours = \SiteKit\Config\hours();

    return (string) wp_json_encode(['tz' => $hours['timezone'], 'week' => $hours['week']]);
}

/** Full years since a founding year, e.g. for "28 Jahre Erfahrung". */
function years_since(int $year): int
{
    return $year > 0 ? max(0, (int) current_time('Y') - $year) : 0;
}

/** Escaped attribute list for <body> classes added by the plugin. */
function body_class(string $class): void
{
    echo 'class="' . esc_attr($class) . '"';
}

/**
 * Inline CSS variables derived from config.php brand colours.
 *
 * This lets a project change the palette in one place without touching the
 * stylesheet. Empty values are skipped, so the CSS defaults stay in charge.
 */
function brand_styles(): void
{
    $colors = (array) \SiteKit\Config\get('brand.colors', []);
    $map    = [
        'primary'      => '--sk-primary',
        'primary_dark' => '--sk-primary-dark',
        'accent'       => '--sk-accent',
        'accent_dark'  => '--sk-accent-dark',
        'link'         => '--sk-link',
    ];

    $css = '';
    foreach ($map as $key => $var) {
        $value = trim((string) ($colors[$key] ?? ''));
        if ($value !== '' && preg_match('/^#[0-9a-fA-F]{3,8}$/', $value)) {
            $css .= $var . ':' . $value . ';';
        }
    }

    if ($css === '') {
        return;
    }

    echo "<style id=\"sk-brand-colors\">:root{" . $css . "}</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values are validated hex colours
}
