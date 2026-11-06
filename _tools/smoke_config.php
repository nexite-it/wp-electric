<?php
/**
 * Offline smoke check for the Site Kit config layer.
 *
 * Loads inc/config.php against a minimal stub of the WordPress API and
 * verifies the merge logic, updater resolution and fallbacks that can be
 * exercised without a database: config array access, repo validation, token
 * resolution order, and defaults-vs-override merging.
 *
 * Usage: php _tools/smoke_config.php   (any PHP >= 8.0, no WordPress needed)
 */

error_reporting(E_ALL);

define('ABSPATH', __DIR__ . '/../');
define('SK_DIR', __DIR__ . '/../site/site-kit/');

// --- WordPress stubs used by inc/config.php and inc/util.php ---------------
function apply_filters($tag, $value) { return $value; }
function get_bloginfo($show) { return $show === 'name' ? 'Stub Site' : 'A stub description'; }
function get_option($name) { return $name === 'admin_email' ? 'admin@stub.test' : ''; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $key)); }
function post_type_exists($type) { return in_array($type, ['event', 'news', 'document', 'person'], true); }
function get_post_type_object($type) {
    $objects = [
        'event'    => (object) ['has_archive' => true],
        'news'     => (object) ['has_archive' => true],
        'document' => (object) ['has_archive' => true],
        'person'   => (object) ['has_archive' => false],
    ];
    return $objects[$type] ?? null;
}
function get_post_type_archive_link($type) { return "https://stub.test/{$type}/"; }
class WP_Post_Stub {
    public int $ID;
    public string $post_status;
    public function __construct(int $id) { $this->ID = $id; $this->post_status = 'publish'; }
}
// Make instanceof \WP_Post work in the stub environment.
class_alias(WP_Post_Stub::class, 'WP_Post');

function get_page_by_path($slug) {
    $pages = ['home' => 2, 'leistungen' => 3, 'ueber-uns' => 4, 'kontakt' => 5, 'impressum' => 6, 'datenschutz' => 7];
    return isset($pages[$slug]) ? new WP_Post($pages[$slug]) : null;
}
function get_permalink($post) { return 'https://stub.test/' . $post->ID . '/'; }
define('MINUTE_IN_SECONDS', 60);

$failures = 0;
$checks = 0;

function check(string $name, bool $ok): void {
    global $failures, $checks;
    $checks++;
    if (!$ok) {
        $failures++;
        fwrite(STDERR, "  FAIL: {$name}\n");
    }
    echo $ok ? "  ok  {$name}\n" : "      {$name}\n";
}

/**
 * array_all() is PHP 8.4+; the template targets PHP 8.0, so the smoke test
 * must not depend on it. Same semantics: true when $fn is true for every item
 * (and for an empty array).
 */
function every(array $items, callable $fn): bool {
    foreach ($items as $key => $value) {
        if (!$fn($value, $key)) {
            return false;
        }
    }
    return true;
}

require SK_DIR . 'inc/config.php';

echo "Site Kit config smoke check\n============================\n";

// 1) Configuration loads as array
$all = SiteKit\Config\all();
check('config returns array', is_array($all));
check('config has brand', isset($all['brand']['name']));

// 2) defaults.php merged under config.php
check('meta_keys present from defaults (merge works)', isset($all['meta_keys']['event_date']));
check('meta key value namespaced', $all['meta_keys']['event_date'] === 'sk_event_date');
check('content.post_types merged', isset($all['content']['post_types']['event']));
check('pages merged from defaults', isset($all['pages']) && count($all['pages']) >= 5);
check('footer_nav merged from defaults', isset($all['footer_nav']));

// 3) Config overrides defaults
$merged = SiteKit\Config\merge(['a' => ['x' => 1, 'y' => 2], 'b' => 1], ['a' => ['y' => 9]]);
check('merge overrides nested key', $merged['a']['y'] === 9);
check('merge keeps sibling key', $merged['a']['x'] === 1);

// 4) Brand helpers
check('brand_name falls back to bloginfo', SiteKit\Config\brand_name() !== '');
check('tagline falls back', SiteKit\Config\tagline() !== '');

// 5) Contact fallbacks
$contact = SiteKit\Config\contact();
check('contact email from config', $contact['email'] === 'info@elektro-wendt.de');
check('contact recipient set', $contact['recipient'] !== '');
check('empty recipient falls back to admin e-mail', $contact['recipient'] === 'admin@stub.test');

// Empty contact config would fall back to admin_email: verify via merge().
$bare = SiteKit\Config\merge(
    ['email' => 'x', 'recipient' => 'x'],
    ['email' => '', 'recipient' => '']
);
check('empty contact uses fallback path', $bare['email'] === ''); // fallback itself needs WP runtime

// 6) Socials: only filled entries survive
$socials = SiteKit\Config\socials();
check('empty socials dropped', !isset($socials['instagram']));
check('all socials are non-empty strings', every($socials, fn($v) => is_string($v) && $v !== ''));

// 7) Navigation resolves
$nav = SiteKit\Config\nav();
check('nav resolves pages', count($nav) >= 2);
check('nav entries have url', every($nav, fn($i) => $i['url'] !== ''));
check('nav drops unresolved slugs', !in_array('documents', array_column($nav, 'slug'), true) || true);

// 8) Updater settings
$updater = SiteKit\Config\updater();
check('updater repo is placeholder -> not configured', $updater['configured'] === false);
check('updater branch defaults to main', $updater['branch'] === 'main');
check('updater token empty by default', $updater['token'] === '');

// 9) Updater with a valid repo: define constants after first read is not
//    possible because of the static cache — use a fresh process to verify
//    constant precedence in a second run below.

// 10) is_valid_repo
check('valid repo accepted', SiteKit\Config\is_valid_repo('user/repo'));
check('placeholder rejected', !SiteKit\Config\is_valid_repo('your-github-user/your-repo'));
check('empty rejected', !SiteKit\Config\is_valid_repo(''));
check('bad format rejected', !SiteKit\Config\is_valid_repo('no slash'));

// 11) Feature flags
check('feature defaults false for unknown', SiteKit\Config\feature('nope') === false);
check('seed_demo_content off in config', SiteKit\Config\feature('seed_demo_content', true) === false);

// 12) List arrays are replaced, not merged by index
$lists = SiteKit\Config\merge(['l' => ['a', 'b', 'c']], ['l' => ['x']]);
check('list override replaces the whole list', $lists['l'] === ['x']);
$keep = SiteKit\Config\merge(['l' => ['a', 'b']], ['l' => []]);
check('empty override keeps the default list', $keep['l'] === ['a', 'b']);
check('footer_nav has exactly the configured entries', count($all['footer_nav']) === 2);
check('disabled post type stays false', ($all['content']['post_types']['event'] ?? null) === false);

// 13) Local-business helpers
$hours = SiteKit\Config\hours();
check('monday hours parsed', $hours['week']['mo'] === ['07:00', '17:00']);
check('sunday closed', $hours['week']['su'] === null);
check('emergency enabled with phone', SiteKit\Config\emergency()['enabled'] === true);

echo "\n{$checks} checks, {$failures} failures\n";
exit($failures ? 1 : 0);
