<?php
/**
 * Base setup: assets, <head>, theme supports, body classes and query scoping.
 *
 * The site does not depend on a theme. Everything a theme would normally
 * provide (templates, header/footer, sizes, supports) comes from here.
 */

namespace SiteKit\Setup;

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

/** Wire up all hooks. */
function run(): void
{
    add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets', 20);
    add_action('after_setup_theme', __NAMESPACE__ . '\\supports');
    add_action('init', __NAMESPACE__ . '\\tidy_head');
    add_action('init', __NAMESPACE__ . '\\register_menus');

    add_action('wp_head', __NAMESPACE__ . '\\js_flag', 0);
    add_action('wp_head', __NAMESPACE__ . '\\brand_styles', 1);
    add_action('wp_head', __NAMESPACE__ . '\\seo_head', 2);
    add_action('wp_head', __NAMESPACE__ . '\\head_icons', 3);
    add_action('wp_head', __NAMESPACE__ . '\\schema', 4);

    add_filter('language_attributes', __NAMESPACE__ . '\\language_attributes');
    add_filter('document_title_parts', __NAMESPACE__ . '\\title_parts');
    add_filter('document_title_separator', __NAMESPACE__ . '\\title_separator');
    add_filter('body_class', __NAMESPACE__ . '\\body_class');
    add_filter('pre_get_posts', __NAMESPACE__ . '\\scope_search');
    add_filter('pre_get_posts', __NAMESPACE__ . '\\scope_blog');
    add_filter('excerpt_length', __NAMESPACE__ . '\\excerpt_length');
    add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');
    add_filter('site_icon_meta_tags', '__return_empty_array');
}

/** Frontend styles and scripts, versioned by file mtime. */
function enqueue_assets(): void
{
    wp_enqueue_style(
        'site-kit',
        Util\asset('css/main.css'),
        [],
        Util\asset_version('css/main.css'),
        'all'
    );

    wp_enqueue_script(
        'site-kit',
        Util\asset('js/main.js'),
        [],
        Util\asset_version('js/main.js'),
        true
    );
}

/** Feature support that a theme would normally declare. */
function supports(): void
{
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', ['height' => 80, 'flex-width' => true, 'flex-height' => true]);

    // Editor colour palette mirrors the CSS tokens so editors can stay on-brand.
    add_theme_support('editor-color-palette', [
        ['name' => __('Primary', 'site-kit'), 'slug' => 'primary', 'color' => (string) Config\get('brand.colors.primary', '#13213a')],
        ['name' => __('Accent', 'site-kit'), 'slug' => 'accent', 'color' => (string) Config\get('brand.colors.accent', '#ff6b1a')],
        ['name' => __('Text', 'site-kit'), 'slug' => 'text', 'color' => '#1d2738'],
        ['name' => __('Muted', 'site-kit'), 'slug' => 'muted', 'color' => '#e3e7ed'],
    ]);
}

/** One navigation location, rendered by the plugin's header partial. */
function register_menus(): void
{
    register_nav_menus([
        'primary' => __('Primary navigation', 'site-kit'),
        'footer'  => __('Footer navigation', 'site-kit'),
    ]);
}

/** Remove emoji, generator and other head noise. */
function tidy_head(): void
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
}

/**
 * Derived brand colours as CSS variables.
 *
 * The stylesheet uses --sk-primary etc. with sane defaults; setting them here
 * from config.php means a project changes its palette without touching CSS.
 */
function brand_styles(): void
{
    Util\brand_styles();
}

/**
 * Mark the document as JS-capable before anything renders.
 *
 * Scroll-reveal styles only hide content under .sk-js, so a visitor without
 * JavaScript (or with a blocked script) still sees every section.
 */
function js_flag(): void
{
    echo "<script>document.documentElement.classList.add('sk-js');</script>\n";
}

/** <html lang> from config (seo.locale), independent of the admin language. */
function language_attributes(string $output): string
{
    $locale = (string) Config\get('seo.locale', '');
    if ($locale === '') {
        return $output;
    }

    $lang = str_replace('_', '-', $locale);
    $out  = preg_replace('/lang="[^"]*"/', 'lang="' . esc_attr($lang) . '"', $output, 1, $count);

    return $count ? (string) $out : trim($output . ' lang="' . esc_attr($lang) . '"');
}

/**
 * <title>: brand name from config instead of the WordPress site title, and
 * the per-page SEO title from Content\page_intro() where one is set.
 *
 * @param array<string, string> $parts
 * @return array<string, string>
 */
function title_parts(array $parts): array
{
    $name  = Config\brand_name();
    $intro = \SiteKit\Content\page_intro(\SiteKit\Template\current_key());
    $seo   = (string) ($intro['seo_title'] ?? '');

    if (is_front_page()) {
        return ['title' => $name, 'tagline' => $seo !== '' ? $seo : Config\tagline()];
    }

    if ($seo !== '') {
        $parts['title'] = $seo;
    }
    unset($parts['tagline']);
    $parts['site'] = $name;

    return $parts;
}

/** Title separator from config. */
function title_separator(string $sep): string
{
    $configured = trim((string) Config\get('seo.title_separator', ''));

    return $configured !== '' ? $configured : $sep;
}

/**
 * Meta description, Open Graph and canonical.
 *
 * Deliberately self-contained: no SEO plugin required for a small site, and
 * SEO plugins detect these tags and step aside.
 */
function seo_head(): void
{
    $name   = Config\brand_name();
    $locale = (string) Config\get('seo.locale', 'en_US');
    $image  = og_image();
    $type   = 'website';
    $title  = wp_get_document_title();
    $intro  = \SiteKit\Content\page_intro(\SiteKit\Template\current_key());
    $desc   = (string) ($intro['seo_desc'] ?? '');

    if (is_singular()) {
        $post = get_queried_object();
        $type = is_page() ? 'website' : 'article';
        if ($post instanceof \WP_Post) {
            if ($desc === '') {
                $desc = Util\excerpt($post, 28);
            }
            $thumb = get_the_post_thumbnail_url($post, 'large');
            if (is_string($thumb) && $thumb !== '') {
                $image = $thumb;
            }
        }
    } elseif (is_archive()) {
        $desc = wp_strip_all_tags(get_the_archive_description());
    }

    if ($desc === '') {
        $desc = (string) Config\get('brand.description', '') ?: Config\tagline();
    }

    $canonical = is_singular() ? (string) get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));

    printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr($name));
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($title));
    printf('<meta property="og:description" content="%s">' . "\n", esc_attr($desc));
    printf('<meta property="og:type" content="%s">' . "\n", esc_attr($type));
    printf('<meta property="og:locale" content="%s">' . "\n", esc_attr($locale));
    printf('<meta property="og:image" content="%s">' . "\n", esc_url($image));
    printf('<meta name="twitter:card" content="%s">' . "\n", esc_attr((string) Config\get('seo.twitter_card', 'summary_large_image')));
    printf('<link rel="canonical" href="%s">' . "\n", esc_url($canonical));
}

/** Absolute URL of the default share image (config value may start with assets/). */
function og_image(): string
{
    $rel = (string) Config\get('seo.og_image', 'assets/img/site/og-default.png');

    return Util\asset((string) preg_replace('#^assets/#', '', $rel));
}

/**
 * schema.org "Electrician" (a LocalBusiness subtype) for local search.
 *
 * Built from config.php only: address, phone, hours, service area and the
 * emergency line. Nothing is printed when no address is configured.
 */
function schema(): void
{
    $contact = Config\contact();
    if ($contact['street'] === '' && $contact['city'] === '') {
        return;
    }

    $postcode = '';
    $locality = $contact['city'];
    if (preg_match('/^(\d{4,5})\s+(.+)$/', $contact['city'], $m)) {
        [$postcode, $locality] = [$m[1], $m[2]];
    }

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Electrician',
        '@id'         => home_url('/#business'),
        'name'        => Config\brand_name(),
        'legalName'   => (string) Config\get('brand.legal_name', ''),
        'description' => (string) Config\get('brand.description', ''),
        'url'         => home_url('/'),
        'logo'        => Util\asset('img/site/logo.svg'),
        'image'       => og_image(),
        'email'       => $contact['email'],
        'telephone'   => substr(Util\tel($contact['phone']), 4),
        'address'     => array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $contact['street'],
            'postalCode'      => $postcode,
            'addressLocality' => $locality,
            'addressCountry'  => 'DE',
        ]),
    ];

    $founded = (int) Config\get('brand.founded', 0);
    if ($founded > 0) {
        $data['foundingDate'] = (string) $founded;
    }

    $towns = array_values(array_filter(array_map('strval', (array) Config\get('service_area', []))));
    if ($towns) {
        $data['areaServed'] = array_map(static fn(string $town): array => ['@type' => 'City', 'name' => $town], $towns);
    }

    $names = ['mo' => 'Monday', 'tu' => 'Tuesday', 'we' => 'Wednesday', 'th' => 'Thursday', 'fr' => 'Friday', 'sa' => 'Saturday', 'su' => 'Sunday'];
    $specs = [];
    foreach (Config\hours()['week'] as $day => $slot) {
        if ($slot === null) {
            continue;
        }
        $key = $slot[0] . '-' . $slot[1];
        $specs[$key] ??= ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => [], 'opens' => $slot[0], 'closes' => $slot[1]];
        $specs[$key]['dayOfWeek'][] = $names[$day];
    }
    if ($specs) {
        $data['openingHoursSpecification'] = array_values($specs);
    }

    $emergency = Config\emergency();
    if ($emergency['enabled']) {
        $data['contactPoint'] = [[
            '@type'             => 'ContactPoint',
            'contactType'       => 'emergency',
            'telephone'         => substr(Util\tel($emergency['phone']), 4),
            'availableLanguage' => 'German',
            'hoursAvailable'    => [
                '@type'     => 'OpeningHoursSpecification',
                'dayOfWeek' => array_values($names),
                'opens'     => '00:00',
                'closes'    => '23:59',
            ],
        ]];
    }

    $socials = array_values(Config\socials());
    if ($socials) {
        $data['sameAs'] = $socials;
    }

    // JSON_HEX_TAG keeps "</script>" in any config value from closing the tag.
    printf(
        "<script type=\"application/ld+json\">%s</script>\n",
        wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    );
}

/** Favicon, theme colour and font preload — all local assets, no CDN. */
function head_icons(): void
{
    $base = Util\asset('img/site/');

    printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url(Util\asset('fonts/archivo-var-latin.woff2')));
    printf('<meta name="theme-color" content="%s">' . "\n", esc_attr((string) Config\get('brand.colors.primary_dark', '#0b1424')));

    if (is_readable(SK_DIR . 'assets/img/site/favicon.svg')) {
        printf('<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url($base . 'favicon.svg'));
    }
    if (is_readable(SK_DIR . 'assets/img/site/favicon.png')) {
        printf('<link rel="icon" href="%s" sizes="64x64">' . "\n", esc_url($base . 'favicon.png'));
    }
    if (is_readable(SK_DIR . 'assets/img/site/apple-touch-icon.png')) {
        printf('<link rel="apple-touch-icon" href="%s">' . "\n", esc_url($base . 'apple-touch-icon.png'));
    }
}

/** Layout body classes. */
function body_class(array $classes): array
{
    $classes[] = 'sk-body';

    if (is_front_page()) {
        $classes[] = 'sk-home';
    }
    if (is_singular()) {
        $classes[] = 'sk-singular';
    }

    return $classes;
}

/** Search covers every public post type. */
function scope_search(\WP_Query $query): \WP_Query
{
    if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return $query;
    }

    $types = ['post', 'page'];
    foreach (get_post_types(['public' => true, '_builtin' => false], 'names') as $type) {
        $types[] = $type;
    }

    $query->set('post_type', $types);

    return $query;
}

/** The blog index shows the 'post' type only (no pages mixed in). */
function scope_blog(\WP_Query $query): \WP_Query
{
    if (is_admin() || !$query->is_main_query() || !$query->is_home()) {
        return $query;
    }

    $query->set('post_type', 'post');

    return $query;
}

function excerpt_length(): int
{
    return 28;
}

function excerpt_more(): string
{
    return '…';
}
