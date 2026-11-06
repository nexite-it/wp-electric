<?php
/**
 * Template system without a theme.
 *
 *  - template_include filter for every public view
 *  - header/footer/page parts as partials inside the plugin
 *  - all templates live in templates/
 *
 * Resolution order is driven by config: pages get the template assigned to
 * them at seed time (`_wp_page_template` = "sk/template-<name>.php"), and
 * every post type falls back to the generic archive/single templates.
 */

namespace SiteKit\Template;

defined('ABSPATH') || exit;

/** Register hooks. */
function register(): void
{
    add_filter('template_include', __NAMESPACE__ . '\\resolve', 99);
    add_filter('theme_page_templates', __NAMESPACE__ . '\\page_templates');
    add_filter('body_class', __NAMESPACE__ . '\\body_class');
    add_shortcode('sk_search_form', __NAMESPACE__ . '\\search_form_shortcode');
    add_shortcode('sk_contact_form', __NAMESPACE__ . '\\contact_form_shortcode');
}

/**
 * Offer the plugin templates in the page editor.
 *
 * Without this filter WordPress rejects the stored `_wp_page_template` value
 * ("sk/template-…") because it is not in the theme's template list.
 *
 * @param array<string, string> $templates
 * @return array<string, string>
 */
function page_templates(array $templates): array
{
    foreach (available() as $file => $label) {
        $templates['sk/' . $file] = $label;
    }

    return $templates;
}

/**
 * Templates that can be assigned to a page, keyed by file name.
 *
 * @return array<string, string>
 */
function available(): array
{
    return [
        'template-start.php'     => __('Site Kit: Start page', 'site-kit'),
        'template-about.php'     => __('Site Kit: About', 'site-kit'),
        'template-team.php'      => __('Site Kit: Team', 'site-kit'),
        'template-contact.php'   => __('Site Kit: Contact', 'site-kit'),
        'template-imprint.php'   => __('Site Kit: Imprint', 'site-kit'),
        'template-privacy.php'   => __('Site Kit: Privacy', 'site-kit'),
        'template-search.php'    => __('Site Kit: Search', 'site-kit'),
        'template-404.php'       => __('Site Kit: 404', 'site-kit'),
    ];
}

/**
 * Pick the template for the current request.
 *
 * @param string $original Template chosen by WordPress.
 */
function resolve(string $original): string
{
    if (is_admin() || is_embed() || is_feed()) {
        return $original;
    }

    // 1) A page with an explicitly assigned plugin template.
    if (is_page()) {
        $object   = get_queried_object();
        $assigned = $object instanceof \WP_Post ? (string) get_page_template_slug($object->ID) : '';

        if (strpos($assigned, 'sk/') === 0) {
            $file = tpl(substr($assigned, 3));
            if ($file !== '') {
                return $file;
            }
        }
    }

    // 2) Front page.
    if (is_front_page()) {
        return tpl('template-start.php') ?: $original;
    }

    // 3) Search and 404.
    if (is_search()) {
        return tpl('template-search.php') ?: $original;
    }
    if (is_404()) {
        return tpl('template-404.php') ?: $original;
    }

    // 4) Singular post types.
    if (is_singular()) {
        $type = (string) get_post_type();

        // A dedicated template per post type: template-single-<type>.php.
        $single = tpl('template-single-' . $type . '.php');
        if ($single !== '') {
            return $single;
        }

        return tpl('template-single.php') ?: $original;
    }

    // 5) Archives of a post type.
    if (is_post_type_archive()) {
        $type = (string) get_query_var('post_type');
        $type = is_array($type) ? (string) reset($type) : $type;

        $archive = tpl('template-archive-' . $type . '.php');
        if ($archive !== '') {
            return $archive;
        }
        // Named archive templates: /events/ → template-events.php.
        $family = tpl('template-' . $type . '.php');
        if ($family !== '') {
            return $family;
        }

        return tpl('template-archive.php') ?: $original;
    }

    // 6) Blog index, category, tag, author and date archives.
    if (is_home() || is_archive()) {
        return tpl('template-archive.php') ?: $original;
    }

    // 7) Any page without an assigned template.
    if (is_page()) {
        return tpl('template-page.php') ?: $original;
    }

    return $original;
}

/** Absolute path to a template, or '' when it does not exist. */
function tpl(string $file): string
{
    $path = SK_DIR . 'templates/' . basename($file);

    return is_readable($path) ? $path : '';
}

/**
 * Render a partial from templates/partials/.
 *
 * @param string                $name Partial file name without .php.
 * @param array<string, mixed>  $vars Extracted into the partial's scope.
 */
function partial(string $name, array $vars = []): void
{
    $path = SK_DIR . 'templates/partials/' . basename($name) . '.php';
    if (!is_readable($path)) {
        return;
    }

    // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- controlled, local scope
    extract($vars, EXTR_SKIP);
    require $path;
}

/**
 * Additional body classes.
 *
 * @param array<int, string> $classes
 * @return array<int, string>
 */
function body_class(array $classes): array
{
    if (is_front_page()) {
        $classes[] = 'sk-home';
    }
    if (is_singular()) {
        $classes[] = 'sk-singular';
    }

    return $classes;
}

/** [sk_search_form] shortcode. */
function search_form_shortcode(): string
{
    ob_start();
    partial('search-form');

    return (string) ob_get_clean();
}

/** [sk_contact_form] shortcode — lets editors drop the form into any page. */
function contact_form_shortcode(): string
{
    ob_start();
    partial('contact-form');

    return (string) ob_get_clean();
}
