<?php
/**
 * Offline smoke check for the carrier theme (functions.php).
 *
 * Verifies the parts of the updater bootstrap that run without WordPress:
 * settings parsing, placeholder detection, constant precedence and token
 * resolution from the shipped config.php.
 *
 * Usage: php _tools/smoke_theme.php
 */

error_reporting(E_ALL);

define('ABSPATH', __DIR__ . '/../');
$theme_dir = ABSPATH;

// --- WordPress stubs the bootstrap touches -------------------------------
function apply_filters($tag, $value) { return $value; }
function get_template_directory() { global $theme_dir; return rtrim($theme_dir, '/'); }
function trailingslashit($path) { return rtrim((string) $path, '/\\') . '/'; }
function wp_normalize_path($path) { return str_replace('\\', '/', (string) $path); }
function wp_mkdir_p($dir) { return true; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function wp_kses_post($s) { return (string) $s; }
function esc_url($s) { return (string) $s; }
function esc_js($s) { return (string) $s; }
function esc_html__($s, $d = null) { return esc_html($s); }
function esc_html_e($s, $d = null) { echo esc_html($s); }
function __($s, $d = null) { return $s; }
function _e($s, $d = null) { echo $s; }
function current_user_can($cap) { return true; }
function get_current_screen() { return (object) ['id' => 'themes']; }
function add_action(...$args) {}
function add_filter(...$args) {}
function get_transient($k) { return false; }
function set_transient($k, $v, $t = 0) { return true; }
function wp_remote_get($url, $args = []) { return new WP_Error('stub', 'offline'); }
function is_wp_error($x) { return $x instanceof WP_Error; }
function wp_remote_retrieve_response_code($r) { return 0; }
class WP_Error { public function __construct($c = '', $m = '') {} }
class WP_Screen {}
define('HOUR_IN_SECONDS', 3600);
define('WP_CONTENT_DIR', sys_get_temp_dir() . '/sk-smoke-wp-content');
define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');

$failures = 0;
$checks = 0;

function check(string $name, bool $ok): void {
    global $failures, $checks;
    $checks++;
    echo $ok ? "  ok  {$name}\n" : "  FAIL {$name}\n";
    if (!$ok) {
        $failures++;
    }
}

echo "Site Kit theme bootstrap smoke check\n====================================\n";

require $theme_dir . 'functions.php';

// functions.php must not have run the PUC bootstrap at this point (file
// missing constants like get_option), but sk_updater_settings() is plain
// code and must be testable.

// 1) Default (placeholder) settings from the shipped config.php
$s = sk_updater_settings();
check('placeholder repo -> not configured', $s['configured'] === false);
check('enabled by default', $s['enabled'] === true);
check('branch defaults to main', $s['branch'] === 'main');
check('token empty without constants', $s['token'] === '');
check('repo is placeholder string', $s['repo'] === 'your-github-user/your-repo');

// 2) The settings must be cached: second call returns the same values
$s2 = sk_updater_settings();
check('settings cached across calls', $s2 === $s);

// 3) Preference: SK_GITHUB_REPO constant beats config.php. Constants cannot
//    be undefined after the first call in a single process (settings are
//    cached), so this is Documented as a limitation and asserted via a
//    subprocess in _tools/check.php instead.
check('sk_updater_settings exists', function_exists('sk_updater_settings'));

// 4) The 404 fallback template exists
check('fallback template exists', file_exists($theme_dir . 'index.php'));
check('fallback template is PHP', is_readable($theme_dir . 'index.php'));

// 5) The shipped plugin tree is complete: every file that sk_sync copies exists
$required = [
    'site/site-kit/site-kit.php',
    'site/site-kit/config.php',
    'site/site-kit/inc/config.php',
    'site/site-kit/inc/defaults.php',
    'site/site-kit/inc/util.php',
    'site/site-kit/inc/icons.php',
    'site/site-kit/inc/setup.php',
    'site/site-kit/inc/cpt.php',
    'site/site-kit/inc/template.php',
    'site/site-kit/inc/content.php',
    'site/site-kit/inc/seed.php',
    'site/site-kit/inc/forms.php',
    'site/sk-loader.php',
];
foreach ($required as $rel) {
    check("shipped file present: {$rel}", is_file($theme_dir . $rel));
}

// 6) Plugin bootstrap requires every module it lists, in order
$bootstrap = (string) file_get_contents($theme_dir . 'site/site-kit/site-kit.php');
preg_match_all("/require_once SK_DIR \. '([^']+)'/", $bootstrap, $m);
check('bootstrap lists modules', count($m[1]) >= 9);
foreach ($m[1] as $rel) {
    check("bootstrap module exists: inc/{$rel}", is_file($theme_dir . 'site/site-kit/' . $rel) || is_file($theme_dir . 'site/site-kit/inc/' . $rel) || is_file($rel));
}

// 7) Every partial referenced by SiteKit\Template\partial(...) exists
$partials_used = [];
foreach (glob($theme_dir . 'site/site-kit/templates/*.php') as $file) {
    $src = (string) file_get_contents($file);
    preg_match_all("/partial\('([a-z0-9-]+)'/", $src, $mm);
    foreach ($mm[1] as $name) {
        $partials_used[$name][] = basename($file);
    }
}
foreach (array_keys($partials_used) as $name) {
    check("partial exists: {$name}", is_file($theme_dir . "site/site-kit/templates/partials/{$name}.php"));
}

// 8) Every template file named in template.php::available() exists
$resolver = (string) file_get_contents($theme_dir . 'site/site-kit/inc/template.php');
preg_match_all("/'((?:template|partial)-[a-z0-9-]+\.php)'/", $resolver, $mm);
foreach (array_unique($mm[1]) as $file) {
    if (strpos($file, 'partial-') === 0) {
        continue;
    }
    check("registered template exists: {$file}", is_file($theme_dir . "site/site-kit/templates/{$file}"));
}

// 9) CSS uses only the sk- prefix (no leftover rc- classes from the source project)
$css = (string) file_get_contents($theme_dir . 'site/site-kit/assets/css/main.css');
check('no rc-* classes in css', strpos($css, '.rc-') === false);

// 10) JS uses only sk- selectors
$js = (string) file_get_contents($theme_dir . 'site/site-kit/assets/js/main.js');
check('no rc- selectors in js', strpos($js, 'rc-') === false);

// 11) Config's pages reference existing templates
$config = require $theme_dir . 'site/site-kit/config.php';
$defaults = require $theme_dir . 'site/site-kit/inc/defaults.php';
$pages = array_merge($defaults['pages'], $config['pages'] ?? []);
foreach ($pages as $page) {
    $tpl = $page['template'] ?? 'page';
    $file = "template-{$tpl}.php";
    check("page '{$page['slug']}' template exists: {$file}", is_file($theme_dir . "site/site-kit/templates/{$file}"));
}

echo "\n{$checks} checks, {$failures} failures\n";
exit($failures ? 1 : 0);
