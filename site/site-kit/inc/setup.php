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

    add_action('wp_head', __NAMESPACE__ . '\\brand_styles', 1);
    add_action('wp_head', __NAMESPACE__ . '\\seo_head', 2);
    add_action('wp_head', __NAMESPACE__ . '\\head_icons', 3);

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
        ['name' => __('Primary', 'site-kit'), 'slug' => 'primary', 'color' => (string) Config\get('brand.colors.primary', '#1d4265')],
        ['name' => __('Accent', 'site-kit'), 'slug' => 'accent', 'color' => (string) Config\get('brand.colors.accent', '#ffa600')],
        ['name' => __('Text', 'site-kit'), 'slug' => 'text', 'color' => '#2d3c4b'],
        ['name' => __('Muted', 'site-kit'), 'slug' => 'muted', 'color' => '#e5e5e8'],
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
 * Meta description, Open Graph and canonical.
 *
 * Deliberately self-contained: no SEO plugin required for a small site, and
 * SEO plugins detect these tags and step aside.
 */
function seo_head(): void
{
    $name  = Config\brand_name();
    $sep   = (string) Config\get('seo.title_separator', '·');
    $locale = (string) Config\get('seo.locale', 'en_US');
    $image = Util\asset((string) Config\get('seo.og_image', 'assets/img/site/og-default.png'));
    $type  = 'website';
    $title = $name;
    $desc  = Config\tagline();

    if (is_singular()) {
        $post  = get_queried_object();
        $title = get_the_title() . ' ' . $sep . ' ' . $name;
        $type  = 'article';
        if ($post instanceof \WP_Post) {
            $desc  = Util\excerpt($post, 28);
            $thumb = get_the_post_thumbnail_url($post, 'large');
            if (is_string($thumb) && $thumb !== '') {
                $image = $thumb;
            }
        }
    } elseif (is_archive()) {
        $title = wp_strip_all_tags(get_the_archive_title()) . ' ' . $sep . ' ' . $name;
        $desc  = wp_strip_all_tags(get_the_archive_description());
        if ($desc === '') {
            $desc = Config\tagline();
        }
    } elseif (is_search()) {
        $title = sprintf(__('Search for “%s”', 'site-kit'), get_search_query()) . ' ' . $sep . ' ' . $name;
    } elseif (is_404()) {
        $title = __('Page not found', 'site-kit') . ' ' . $sep . ' ' . $name;
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

/** Favicon, theme colour and font preload — all local assets, no CDN. */
function head_icons(): void
{
    $base = Util\asset('img/site/');

    printf('<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url(Util\asset('fonts/archivo-var-latin.woff2')));
    printf('<meta name="theme-color" content="%s">' . "\n", esc_attr((string) Config\get('brand.colors.primary', '#1d4265')));

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
