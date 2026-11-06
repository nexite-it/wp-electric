<?php
/**
 * Post types, taxonomies and meta fields — all generated from config.php.
 *
 * Nothing in this file names a specific post type. Add one to
 * config.php → content → post_types and it is registered here, gets admin
 * columns, meta boxes and archive/single templates automatically.
 */

namespace SiteKit\CPT;

defined('ABSPATH') || exit;

use SiteKit\Config;

/** Register hooks. */
function run(): void
{
    add_action('init', __NAMESPACE__ . '\\register', 5);
    add_action('add_meta_boxes', __NAMESPACE__ . '\\meta_boxes');
    add_action('save_post', __NAMESPACE__ . '\\save_meta', 10, 2);
    add_filter('manage_posts_columns', __NAMESPACE__ . '\\columns');
    add_action('manage_posts_custom_column', __NAMESPACE__ . '\\column_content', 10, 2);
}

/** Registered post type config, keyed by name. */
function post_types(): array
{
    return (array) Config\get('content.post_types', []);
}

/** Registered taxonomy config, keyed by name. */
function taxonomies(): array
{
    return (array) Config\get('content.taxonomies', []);
}

/** Field definitions for a post type. */
function fields(string $post_type): array
{
    $fields = Config\get('content.meta.' . $post_type, []);

    return is_array($fields) ? array_values(array_filter($fields, 'is_array')) : [];
}

/**
 * Meta key for a field, resolved through config so projects can rename keys.
 *
 * @return string
 */
function meta_key(string $post_type, string $suffix, string $default = ''): string
{
    $keys = (array) Config\get('meta_keys', []);

    return (string) ($keys[$suffix] ?? $default ?: $suffix);
}

/** Register all post types and taxonomies from config. */
function register(): void
{
    $defaults = [
        'public'             => true,
        'publicly_queryable' => true,
        'show_in_rest'       => true,
        'show_ui'            => true,
        'show_in_nav_menus'  => true,
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-admin-page',
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
        'rewrite'            => [],
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    ];

    foreach (post_types() as $name => $conf) {
        if (!is_array($conf) || !preg_match('/^[a-z0-9_-]{1,20}$/', (string) $name)) {
            continue;
        }
        $conf = array_replace($defaults, $conf);

        $labels = (array) ($conf['labels'] ?? []);
        $conf['labels'] = array_replace([
            'name'          => ucfirst((string) $name),
            'singular_name' => ucfirst((string) $name),
            'add_new_item'  => 'Add new',
            'edit_item'     => 'Edit',
            'all_items'     => 'All items',
            'search_items'  => 'Search',
            'not_found'     => 'Nothing found',
        ], $labels);

        // A plain string rewrite becomes ['slug' => ...] + archive defaults.
        if (is_string($conf['rewrite']) && $conf['rewrite'] !== '') {
            $conf['rewrite'] = ['slug' => $conf['rewrite'], 'with_front' => false];
        } elseif ($conf['rewrite'] === true) {
            $conf['rewrite'] = ['slug' => (string) $name, 'with_front' => false];
        }

        if ($conf['has_archive'] === true) {
            $conf['has_archive'] = (string) $name;
        }

        // Register taxonomies for this type after the type exists.
        $taxes = (array) ($conf['taxonomies'] ?? []);
        unset($conf['taxonomies']);

        register_post_type((string) $name, $conf); // phpcs:ignore WordPress.WP.PostTypeRegistration

        foreach ($taxes as $tax) {
            if (is_string($tax) && taxonomy_exists($tax)) {
                register_taxonomy_for_object_type($tax, (string) $name);
            }
        }
    }

    $tax_defaults = [
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'query_var'         => true,
    ];

    foreach (taxonomies() as $name => $conf) {
        if (!is_array($conf) || !preg_match('/^[a-z0-9_-]{1,32}$/', (string) $name)) {
            continue;
        }
        $conf = array_replace($tax_defaults, $conf);

        $labels = (array) ($conf['labels'] ?? []);
        $conf['labels'] = array_replace([
            'name'          => ucfirst((string) $name),
            'singular_name' => ucfirst((string) $name),
            'add_new_item'  => 'Add new',
            'all_items'     => 'All items',
        ], $labels);

        if (is_string($conf['rewrite']) && $conf['rewrite'] !== '') {
            $conf['rewrite'] = ['slug' => $conf['rewrite'], 'with_front' => false];
        }

        $objects = (array) ($conf['post_types'] ?? []);
        unset($conf['post_types'], $conf['post_type']);

        register_taxonomy((string) $name, $objects ?: ['post'], $conf);
    }
}

/** Whether the current screen is a configured post type. */
function is_managed_screen(?\WP_Screen $screen = null): bool
{
    $screen = $screen ?: (function_exists('get_current_screen') ? get_current_screen() : null);

    return $screen instanceof \WP_Screen && $screen->base === 'post' && array_key_exists((string) $screen->post_type, post_types());
}

/** One meta box per `box` label, grouped in config order. */
function meta_boxes(): void
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen instanceof \WP_Screen) {
        return;
    }

    $post_type = (string) $screen->post_type;
    $fields    = fields($post_type);
    if (!$fields) {
        return;
    }

    $boxes = [];
    foreach ($fields as $field) {
        $box = (string) ($field['box'] ?? __('Details', 'site-kit'));
        $boxes[$box][] = $field;
    }

    foreach ($boxes as $title => $box_fields) {
        add_meta_box(
            'sk_meta_' . sanitize_key($post_type . '_' . $title),
            $title,
            function ($post) use ($box_fields): void {
                render_box($post, $box_fields);
            },
            $post_type,
            'normal',
            'default'
        );
    }
}

/**
 * Render a meta box.
 *
 * @param \WP_Post            $post
 * @param array<int, array>   $fields
 */
function render_box($post, array $fields): void
{
    wp_nonce_field('sk_meta_' . $post->post_type, 'sk_meta_nonce');
    echo '<div class="sk-meta">';

    foreach ($fields as $field) {
        $key   = (string) ($field['key'] ?? '');
        if ($key === '') {
            continue;
        }
        $type  = (string) ($field['type'] ?? 'text');
        $label = (string) ($field['label'] ?? $key);
        $value = get_post_meta($post->ID, $key, true);
        if ($value === '' && isset($field['default'])) {
            $value = $field['default'];
        }
        $help  = (string) ($field['help'] ?? '');
        $width = ($field['width'] ?? 'full') === 'half' ? ' sk-meta__field--half' : '';

        echo '<p class="sk-meta__field' . esc_attr($width) . '">';
        printf('<label class="sk-meta__label" for="%1$s">%2$s</label>', esc_attr($key), esc_html($label));

        switch ($type) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s>',
                    esc_attr($key),
                    checked($value, '1', false)
                );
                break;

            case 'select':
                printf('<select id="%1$s" name="%1$s">', esc_attr($key));
                foreach ((array) ($field['options'] ?? []) as $opt_value => $opt_label) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr((string) $opt_value),
                        selected((string) $value, (string) $opt_value, false),
                        esc_html((string) $opt_label)
                    );
                }
                echo '</select>';
                break;

            case 'textarea':
                printf(
                    '<textarea id="%1$s" name="%1$s" rows="4" class="widefat">%2$s</textarea>',
                    esc_attr($key),
                    esc_textarea((string) $value)
                );
                break;

            case 'date':
                printf(
                    '<input type="date" id="%1$s" name="%1$s" value="%2$s" class="widefat">',
                    esc_attr($key),
                    esc_attr((string) $value)
                );
                break;

            case 'time':
                printf(
                    '<input type="time" id="%1$s" name="%1$s" value="%2$s" class="widefat">',
                    esc_attr($key),
                    esc_attr((string) $value)
                );
                break;

            default:
                printf(
                    '<input type="%3$s" id="%1$s" name="%1$s" value="%2$s" class="widefat">',
                    esc_attr($key),
                    esc_attr((string) $value),
                    esc_attr(in_array($type, ['url', 'email', 'number', 'tel'], true) ? $type : 'text')
                );
        }

        if ($help !== '') {
            printf('<span class="sk-meta__help">%s</span>', esc_html($help));
        }
        echo '</p>';
    }

    // Shared styling: small enough to not warrant an enqueued stylesheet.
    echo '<style>.sk-meta{display:flex;flex-wrap:wrap;gap:4px 18px;padding:6px 0}'
        . '.sk-meta__field{flex:1 1 100%;margin:0 0 10px}'
        . '.sk-meta__field--half{flex:1 1 calc(50% - 18px);min-width:220px}'
        . '.sk-meta__label{display:block;font-weight:600;margin-bottom:4px}'
        . '.sk-meta__help{display:block;color:#646970;font-size:12px;margin-top:4px}</style>';

    echo '</div>';
}

/** Persist meta fields with nonce and capability checks. */
function save_meta(int $post_id, $post): void
{
    if (!$post instanceof \WP_Post) {
        return;
    }
    if (!isset($_POST['sk_meta_nonce']) || !wp_verify_nonce(sanitize_key(wp_unslash((string) $_POST['sk_meta_nonce'])), 'sk_meta_' . $post->post_type)) {
        return;
    }
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!array_key_exists($post->post_type, post_types())) {
        return;
    }

    foreach (fields($post->post_type) as $field) {
        $key  = (string) ($field['key'] ?? '');
        $type = (string) ($field['type'] ?? 'text');
        if ($key === '') {
            continue;
        }

        if ($type === 'checkbox') {
            update_post_meta($post_id, $key, isset($_POST[$key]) ? '1' : '0');
            continue;
        }

        $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
        if (!is_scalar($raw)) {
            continue;
        }

        $value = match ($type) {
            'url'      => esc_url_raw((string) $raw),
            'email'    => sanitize_email((string) $raw),
            'number'   => (string) (int) $raw,
            'textarea' => sanitize_textarea_field((string) $raw),
            'date'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $raw) ? (string) $raw : '',
            'time'     => preg_match('/^\d{2}:\d{2}$/', (string) $raw) ? (string) $raw : '',
            'select'   => array_key_exists((string) $raw, (array) ($field['options'] ?? [])) ? (string) $raw : '',
            default    => sanitize_text_field((string) $raw),
        };

        if ($value === '') {
            delete_post_meta($post_id, $key);
        } else {
            update_post_meta($post_id, $key, $value);
        }
    }
}

/**
 * Add a column for the first date field of each managed post type.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
function columns(array $columns): array
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen instanceof \WP_Screen) {
        return $columns;
    }

    $date_field = first_field_of_type((string) $screen->post_type, ['date']);
    if ($date_field === null) {
        return $columns;
    }

    $out = [];
    foreach ($columns as $key => $label) {
        $out[$key] = $label;
        if ($key === 'title') {
            $out['sk_meta_date'] = (string) ($date_field['label'] ?? __('Date', 'site-kit'));
        }
    }

    return $out;
}

/** Column content for the generated date column. */
function column_content(string $column, int $post_id): void
{
    if ($column !== 'sk_meta_date') {
        return;
    }

    $post       = get_post($post_id);
    $date_field = $post ? first_field_of_type($post->post_type, ['date']) : null;
    $value      = $date_field ? (string) get_post_meta($post_id, (string) $date_field['key'], true) : '';

    if ($value === '') {
        echo '—';
        return;
    }

    echo esc_html((string) date_i18n(get_option('date_format') ?: 'Y-m-d', strtotime($value)));
}

/**
 * First field of one of the given types for a post type.
 *
 * @param array<int, string> $types
 * @return array<string, mixed>|null
 */
function first_field_of_type(string $post_type, array $types): ?array
{
    foreach (fields($post_type) as $field) {
        if (in_array((string) ($field['type'] ?? 'text'), $types, true)) {
            return $field;
        }
    }

    return null;
}
