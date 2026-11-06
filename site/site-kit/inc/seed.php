<?php
/**
 * Seeder: creates pages, navigation, the front page and (optionally) demo
 * content on the first admin request after activation.
 *
 * Design decisions that matter in production:
 *  - idempotent: existing pages and menus are never duplicated or overwritten
 *  - self-healing: a missing page or template is repaired on the next visit
 *  - reversible: "Reset demo content" deletes only what the seeder created
 *  - no destructive defaults: the reset button never touches manually created
 *    pages or media that the site owner uploaded
 */

namespace SiteKit\Seed;

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;

/** Option keys, versioned so a schema change can re-run the seeder. */
const OPTION_SEEDED  = 'sk_seeded_v1';
const OPTION_REPAIR  = 'sk_repaired_version';
const OPTION_REWRITE = 'sk_rewrite_signature';

/** Register hooks. */
function init(): void
{
    add_action('admin_init', __NAMESPACE__ . '\\maybe_seed');
    add_action('admin_menu', __NAMESPACE__ . '\\admin_page');
    add_action('admin_post_sk_reseed', __NAMESPACE__ . '\\handle_reseed');
    add_action('admin_post_sk_repair', __NAMESPACE__ . '\\handle_repair');
    add_action('init', __NAMESPACE__ . '\\maybe_flush_rewrites', 99);
}

/**
 * Flush rewrite rules once per version/permalink-structure combination.
 *
 * Without this, archive and page URLs 404 on a fresh install: WordPress keeps
 * the old rules until something flushes them. Running it on every request
 * would be far too expensive, hence the signature option.
 */
function maybe_flush_rewrites(): void
{
    $signature = md5((string) get_option('permalink_structure') . '|' . SK_VERSION);

    if (get_option(OPTION_REWRITE) === $signature) {
        return;
    }

    flush_rewrite_rules(false);
    update_option(OPTION_REWRITE, $signature);
}

/** Run the seeder once, then keep pages healthy. */
function maybe_seed(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!get_option(OPTION_SEEDED)) {
        seed();
        return;
    }

    maybe_repair();
}

/**
 * Repair pages that went missing and re-flush rewrites after a version bump.
 *
 * Cheap enough to run on admin_init: it stops after one pass per version and
 * only touches pages that are actually missing or mistemplated.
 */
function maybe_repair(): void
{
    if (get_option(OPTION_REPAIR) === SK_VERSION) {
        return;
    }

    repair_pages();
    flush_rewrite_rules(false);
    update_option(OPTION_REWRITE, md5((string) get_option('permalink_structure') . '|' . SK_VERSION));
    update_option(OPTION_REPAIR, SK_VERSION);
}

/**
 * Page definitions from config.
 *
 * @return array<int, array{slug: string, title: string, template: string, front: bool}>
 */
function pages_config(): array
{
    $pages = [];
    foreach ((array) Config\get('pages', []) as $page) {
        if (!is_array($page) || empty($page['slug'])) {
            continue;
        }
        $pages[] = [
            'slug'     => sanitize_title((string) $page['slug']),
            'title'    => (string) ($page['title'] ?? $page['slug']),
            'template' => (string) ($page['template'] ?? 'page'),
            'front'    => !empty($page['front']),
        ];
    }

    return $pages;
}

/** Create everything. Safe to call repeatedly. */
function seed(): void
{
    $ids = create_pages();

    set_front_page($ids);
    create_menu();

    update_option('permalink_structure', '/%postname%/');
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');

    if (Config\feature('seed_demo_content', true)) {
        seed_content();
    }

    update_option(OPTION_SEEDED, 1);
    update_option(OPTION_REPAIR, SK_VERSION);

    // 'init' has already run at this point, so flush immediately.
    flush_rewrite_rules(false);
    update_option(OPTION_REWRITE, md5((string) get_option('permalink_structure') . '|' . SK_VERSION));
}

/**
 * Create the configured pages and assign their templates.
 *
 * @return array<string, int> Page IDs keyed by slug.
 */
function create_pages(): array
{
    $ids = [];

    foreach (pages_config() as $page) {
        $existing = get_page_by_path($page['slug']);

        if ($existing instanceof \WP_Post) {
            $ids[$page['slug']] = (int) $existing->ID;
            continue;
        }

        $id = wp_insert_post([
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
        ], true);

        if (is_wp_error($id) || !$id) {
            continue;
        }

        $ids[$page['slug']] = (int) $id;

        // The template must be stored as meta: 'page_template' is not a valid
        // wp_insert_post field and is silently ignored.
        update_post_meta((int) $id, '_wp_page_template', 'sk/template-' . $page['template'] . '.php');
    }

    return $ids;
}

/**
 * Make the configured page the static front page.
 *
 * @param array<string, int> $ids
 */
function set_front_page(array $ids): void
{
    $front = null;
    foreach (pages_config() as $page) {
        if ($page['front'] && isset($ids[$page['slug']])) {
            $front = $ids[$page['slug']];
            break;
        }
    }

    if ($front === null) {
        return;
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $front);

    // The blog index falls back to the news post type archive: there is no
    // dedicated posts page, so WordPress does not need one.
    update_option('page_for_posts', 0);
}

/**
 * Build the primary menu from config.php → nav.
 *
 * The menu is created once and then left alone, so an editor can reorder it in
 * the admin without the next deploy resetting it.
 */
function create_menu(): void
{
    $menu_name = __('Primary', 'site-kit');
    $menu      = wp_get_nav_menu_object($menu_name);

    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return;
        }
    } else {
        $menu_id = (int) $menu->term_id;
    }

    // Only populate an empty menu; never duplicate or reorder existing items.
    $existing = wp_get_nav_menu_items($menu_id);
    if (!$existing) {
        foreach ((array) Config\get('nav', []) as $item) {
            if (!is_array($item) || empty($item['slug'])) {
                continue;
            }
            $slug = (string) $item['slug'];
            $url  = Config\resolve_location($slug);
            if ($url === '') {
                continue;
            }

            // Page → a page menu item; post type slug → archive menu item;
            // anything else → custom link.
            $is_archive = post_type_exists($slug) && get_post_type_object($slug)->has_archive;
            $page       = $is_archive ? null : get_page_by_path($slug);

            if ($is_archive) {
                $args = [
                    'menu-item-type'   => 'post_type_archive',
                    'menu-item-object' => $slug,
                    'menu-item-url'    => $url,
                ];
            } elseif ($page instanceof \WP_Post) {
                $args = [
                    'menu-item-type'      => 'post_type',
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => (int) $page->ID,
                ];
            } else {
                $args = [
                    'menu-item-type' => 'custom',
                    'menu-item-url'  => $url,
                ];
            }

            wp_update_nav_menu_item($menu_id, 0, $args + [
                'menu-item-title'  => (string) ($item['label'] ?? $slug),
                'menu-item-status' => 'publish',
            ]);
        }
    }

    $locations            = (array) get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = (int) $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

/** Demo posts, terms and people. */
function seed_content(): void
{
    seed_people();
    seed_events();
    seed_news();
    seed_documents();
}

/** Insert a post once, keyed by a stable meta marker. */
function insert_once(string $type, string $marker, array $args): int
{
    $existing = get_posts([
        'post_type'      => $type,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_key'       => '_sk_demo',
        'meta_value'     => $marker,
    ]);

    if ($existing) {
        return (int) $existing[0];
    }

    $id = wp_insert_post(array_merge([
        'post_type'   => $type,
        'post_status' => 'publish',
    ], $args), true);

    if (is_wp_error($id) || !$id) {
        return 0;
    }

    update_post_meta((int) $id, '_sk_demo', $marker);

    return (int) $id;
}

function seed_people(): void
{
    if (!post_type_exists('person')) {
        return;
    }

    foreach (Content\people() as $i => $person) {
        $id = insert_once('person', 'person:' . $i, [
            'post_title' => $person['name'],
            'menu_order' => $i,
        ]);
        if (!$id) {
            continue;
        }
        update_post_meta($id, 'sk_person_role', $person['role']);
        update_post_meta($id, 'sk_person_group', $person['group']);
        if (!empty($person['email'])) {
            update_post_meta($id, 'sk_person_email', $person['email']);
        }
    }
}

function seed_events(): void
{
    if (!post_type_exists('event')) {
        return;
    }

    foreach (Content\events() as $i => $event) {
        $id = insert_once('event', 'event:' . $i, [
            'post_title'   => $event['title'],
            'post_excerpt' => $event['excerpt'],
            'post_content' => '<p>' . esc_html($event['body']) . '</p>',
        ]);
        if (!$id) {
            continue;
        }
        update_post_meta($id, 'sk_event_date', $event['date']);
        update_post_meta($id, 'sk_event_time', $event['time']);
        update_post_meta($id, 'sk_event_location', $event['location']);
        update_post_meta($id, 'sk_event_registration', $event['registration'] ? '1' : '0');

        if (!empty($event['type'])) {
            wp_set_object_terms($id, $event['type'], 'event_type', false);
        }
    }
}

function seed_news(): void
{
    if (!post_type_exists('news')) {
        return;
    }

    foreach (Content\news() as $i => $item) {
        insert_once('news', 'news:' . $i, [
            'post_title'   => $item['title'],
            'post_excerpt' => $item['excerpt'],
            'post_content' => '<p>' . esc_html($item['body']) . '</p>',
            'post_date'    => $item['date'] . ' 10:00:00',
        ]);
    }
}

function seed_documents(): void
{
    if (!post_type_exists('document')) {
        return;
    }

    foreach (Content\documents() as $i => $doc) {
        $id = insert_once('document', 'document:' . $i, [
            'post_title'   => $doc['title'],
            'post_excerpt' => $doc['excerpt'],
            'post_content' => '',
            'menu_order'   => $i,
        ]);
        if (!$id) {
            continue;
        }
        update_post_meta($id, 'sk_document_date', $doc['date']);

        if (!empty($doc['type'])) {
            wp_set_object_terms($id, $doc['type'], 'document_type', false);
        }
    }
}

/**
 * Delete demo content created by the seeder (posts marked with _sk_demo).
 *
 * @return int Number of deleted posts.
 */
function delete_demo_content(): int
{
    $posts = get_posts([
        'post_type'      => array_keys((array) Config\get('content.post_types', [])),
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_key'       => '_sk_demo',
    ]);

    $count = 0;
    foreach ($posts as $id) {
        if (wp_delete_post((int) $id, true)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Ensure every configured page exists with the right template.
 *
 * @return int Number of repaired pages.
 */
function repair_pages(): int
{
    $fixed = 0;

    foreach (pages_config() as $page) {
        $want     = 'sk/template-' . $page['template'] . '.php';
        $existing = get_page_by_path($page['slug']);

        if ($existing instanceof \WP_Post) {
            if ((string) get_page_template_slug($existing->ID) !== $want) {
                update_post_meta((int) $existing->ID, '_wp_page_template', $want);
                $fixed++;
            }
            continue;
        }

        $id = wp_insert_post([
            'post_title'  => $page['title'],
            'post_name'   => $page['slug'],
            'post_type'   => 'page',
            'post_status' => 'publish',
        ], true);

        if (!is_wp_error($id) && $id) {
            update_post_meta((int) $id, '_wp_page_template', $want);
            $fixed++;
        }
    }

    if (get_option('permalink_structure') !== '/%postname%/') {
        update_option('permalink_structure', '/%postname%/');
    }

    $ids = [];
    foreach (pages_config() as $page) {
        $found = get_page_by_path($page['slug']);
        if ($found instanceof \WP_Post) {
            $ids[$page['slug']] = (int) $found->ID;
        }
    }
    set_front_page($ids);

    return $fixed;
}

/* -------------------------------------------------------------------------
 * Admin tools
 * ---------------------------------------------------------------------- */

/** Tools → Site Kit. */
function admin_page(): void
{
    add_submenu_page(
        'tools.php',
        __('Site Kit', 'site-kit'),
        __('Site Kit', 'site-kit'),
        'manage_options',
        'site-kit',
        __NAMESPACE__ . '\\render_admin'
    );
}

/** Diagnostics + reset buttons. */
function render_admin(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $seeded    = (bool) get_option(OPTION_SEEDED);
    $reseed    = wp_nonce_url(admin_url('admin-post.php?action=sk_reseed'), 'sk_reseed');
    $repair    = wp_nonce_url(admin_url('admin-post.php?action=sk_repair'), 'sk_repair');
    $permalink = (string) get_option('permalink_structure');

    $rows    = [];
    $broken  = 0;
    foreach (pages_config() as $page) {
        $found = get_page_by_path($page['slug']);
        $state = __('ok', 'site-kit');
        $url   = '';

        if (!$found instanceof \WP_Post || $found->post_status !== 'publish') {
            $state = __('missing', 'site-kit');
            $broken++;
        } else {
            $url = (string) get_permalink($found->ID);
            if ((string) get_page_template_slug($found->ID) === '') {
                $state = __('no template', 'site-kit');
                $broken++;
            }
        }

        $rows[] = ['label' => $page['title'], 'slug' => $page['slug'], 'state' => $state, 'url' => $url];
    }

    $updater = Config\updater();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(sprintf(__('%s — Site Kit', 'site-kit'), Config\brand_name())); ?></h1>

        <p>
            <?php esc_html_e('Seed status:', 'site-kit'); ?>
            <?php if ($seeded) : ?>
                <strong style="color:#1f7a4d"><?php esc_html_e('seeded', 'site-kit'); ?></strong>
            <?php else : ?>
                <strong style="color:#b42318"><?php esc_html_e('not seeded yet', 'site-kit'); ?></strong>
            <?php endif; ?>
            &middot; <?php esc_html_e('Kit version:', 'site-kit'); ?> <code><?php echo esc_html(SK_VERSION); ?></code>
        </p>

        <h2><?php esc_html_e('Pages', 'site-kit'); ?></h2>
        <table class="widefat striped" style="max-width:900px">
            <thead>
                <tr>
                    <th><?php esc_html_e('Page', 'site-kit'); ?></th>
                    <th><?php esc_html_e('Slug', 'site-kit'); ?></th>
                    <th><?php esc_html_e('Status', 'site-kit'); ?></th>
                    <th><?php esc_html_e('URL', 'site-kit'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row) : ?>
                <tr>
                    <td><strong><?php echo esc_html($row['label']); ?></strong></td>
                    <td><code><?php echo esc_html($row['slug']); ?></code></td>
                    <td>
                        <?php if ($row['state'] === 'ok') : ?>
                            <span style="color:#1f7a4d"><?php esc_html_e('ok', 'site-kit'); ?></span>
                        <?php else : ?>
                            <span style="color:#b42318"><?php echo esc_html($row['state']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['url'] !== '') : ?>
                            <a href="<?php echo esc_url($row['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html($row['url']); ?></a>
                        <?php else : ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:14px">
            <?php esc_html_e('Permalinks:', 'site-kit'); ?>
            <?php if ($permalink === '/%postname%/') : ?>
                <span style="color:#1f7a4d"><code><?php echo esc_html($permalink); ?></code></span>
            <?php else : ?>
                <span style="color:#b42318"><code><?php echo esc_html($permalink !== '' ? $permalink : '(empty)'); ?></code></span>
            <?php endif; ?>
        </p>

        <?php if ($broken > 0) : ?>
            <div class="notice notice-error inline" style="margin:14px 0">
                <p><?php echo esc_html(sprintf(_n('%d problem found.', '%d problems found.', $broken, 'site-kit'), $broken)); ?></p>
            </div>
            <p><a class="button button-primary" href="<?php echo esc_url($repair); ?>"><?php esc_html_e('Repair pages', 'site-kit'); ?></a></p>
        <?php endif; ?>

        <h2><?php esc_html_e('GitHub updater', 'site-kit'); ?></h2>
        <table class="widefat striped" style="max-width:900px">
            <tbody>
                <tr>
                    <th style="width:200px"><?php esc_html_e('Repository', 'site-kit'); ?></th>
                    <td><code><?php echo esc_html($updater['repo'] !== '' ? $updater['repo'] : '—'); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Branch', 'site-kit'); ?></th>
                    <td><code><?php echo esc_html($updater['branch']); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Access token', 'site-kit'); ?></th>
                    <td>
                        <?php if ($updater['token'] !== '') : ?>
                            <span style="color:#1f7a4d"><?php esc_html_e('configured', 'site-kit'); ?></span>
                        <?php else : ?>
                            <span style="color:#b42318"><?php esc_html_e('not configured (public repositories only)', 'site-kit'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if (!$updater['configured']) : ?>
            <div class="notice notice-warning inline" style="margin:14px 0">
                <p>
                    <?php
                    echo wp_kses_post(sprintf(
                        /* translators: %s: config file path. */
                        __('The updater is not configured yet. Set <code>updater.repo</code> to <code>owner/repository</code> in %s to enable theme updates.', 'site-kit'),
                        '<code>site/site-kit/config.php</code>'
                    ));
                    ?>
                </p>
            </div>
        <?php else : ?>
            <p>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('update-core.php?force-check=1'), 'force-check')); ?>">
                    <?php esc_html_e('Check for updates now', 'site-kit'); ?>
                </a>
            </p>
        <?php endif; ?>

        <h2><?php esc_html_e('Demo content', 'site-kit'); ?></h2>
        <p><?php esc_html_e('The seeder creates the pages above, the navigation and sample events, news, documents and people. Resetting removes only content the seeder created — your own pages, posts and media are kept.', 'site-kit'); ?></p>
        <p>
            <a class="button" href="<?php echo esc_url($repair); ?>"><?php esc_html_e('Repair pages', 'site-kit'); ?></a>
            <a class="button button-link-delete" href="<?php echo esc_url($reseed); ?>"
               onclick="return confirm('<?php echo esc_js(__('Delete all demo content and re-create it?', 'site-kit')); ?>');">
                <?php esc_html_e('Reset demo content', 'site-kit'); ?>
            </a>
        </p>
    </div>
    <?php
}

/** Reset demo content, then re-seed it. */
function handle_reseed(): void
{
    if (!current_user_can('manage_options') || !check_admin_referer('sk_reseed')) {
        wp_die(esc_html__('You are not allowed to do this.', 'site-kit'));
    }

    delete_demo_content();
    seed_content();
    flush_rewrite_rules(false);

    wp_safe_redirect(admin_url('tools.php?page=site-kit&reseeded=1'));
    exit;
}

/** Repair missing pages/templates. */
function handle_repair(): void
{
    if (!current_user_can('manage_options') || !check_admin_referer('sk_repair')) {
        wp_die(esc_html__('You are not allowed to do this.', 'site-kit'));
    }

    repair_pages();
    flush_rewrite_rules(false);
    update_option(OPTION_REWRITE, md5((string) get_option('permalink_structure') . '|' . SK_VERSION));

    wp_safe_redirect(admin_url('tools.php?page=site-kit&repaired=1'));
    exit;
}

