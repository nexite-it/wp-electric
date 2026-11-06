<?php
/**
 * Carrier theme: installs/updates the Site Kit plugin and updates itself from
 * GitHub.
 *
 * The theme deliberately renders nothing. It has three jobs:
 *
 *   1. be a valid, activation-ready WordPress theme (WordPress insists on one)
 *   2. copy site/ into wp-content/mu-plugins/ on activation and after updates
 *   3. check GitHub for new versions of this repository
 *
 * Deployment flow: edit → push to the configured branch → WordPress shows
 * "update available" → updating replaces the theme and re-syncs site/.
 *
 * Project settings (repository, branch, token constant) live in
 * site/site-kit/config.php so a new project only edits one file. Only the
 * bootstrap constants below must stay in this file, because the plugin is not
 * loaded yet at this point.
 */

defined('ABSPATH') || exit;

/** Path to the shipped plugin inside the theme. */
const SK_SYNC_SOURCE = 'site/';

/* ---------------------------------------------------------------------------
 * 1) Sync: ship the mu-plugin from site/ into wp-content/mu-plugins/
 * ------------------------------------------------------------------------ */

if (!function_exists('sk_sync_source_dir')) {
    /** Absolute path of the plugin source inside the theme. */
    function sk_sync_source_dir(): string
    {
        return trailingslashit(get_template_directory()) . SK_SYNC_SOURCE;
    }
}

if (!function_exists('sk_sync')) {
    /**
     * Copy site/ → wp-content/mu-plugins/, skipping unchanged files.
     *
     * Deliberately additive: files are only written when changed and nothing is
     * ever deleted, so a bad deploy can never wipe a working mu-plugins folder.
     *
     * @param bool $force Write every file even when hashes match.
     * @return bool True when at least one file was (re)written.
     */
    function sk_sync(bool $force = false): bool
    {
        $source = sk_sync_source_dir();
        if (!is_dir($source)) {
            return false;
        }

        $target_root = trailingslashit(WP_CONTENT_DIR) . 'mu-plugins/';
        if (!wp_mkdir_p($target_root)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $written = false;

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            // Keep the repository's ignore rules out of the deployed plugin.
            if (in_array($file->getFilename(), ['.DS_Store', 'Thumbs.db', '.gitignore'], true)) {
                continue;
            }

            $relative = ltrim(str_replace(wp_normalize_path($source), '', wp_normalize_path($file->getPathname())), '/');
            $target   = $target_root . $relative;

            $dir = dirname($target);
            if (!is_dir($dir) && !wp_mkdir_p($dir)) {
                continue;
            }

            if (!$force && file_exists($target) && hash_file('sha256', $file->getPathname()) === hash_file('sha256', $target)) {
                continue;
            }

            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
            if (@file_put_contents($target, file_get_contents($file->getPathname())) !== false) {
                $written = true;
            }
        }

        // A version bump forces the plugin's own rewrite flush/repair pass.
        if ($written) {
            delete_option('sk_repaired_version');
        }

        return $written;
    }
}

// Install/refresh on theme activation.
add_action('after_switch_theme', function (): void {
    sk_sync(true);
});

// Re-sync after any theme update, whoever triggered it.
add_action('upgrader_process_complete', function ($upgrader, $data = []): void {
    if (is_array($data) && ($data['type'] ?? '') === 'theme') {
        sk_sync(true);
    }
}, 10, 2);

// Safety net: if the plugin is missing entirely (fresh clone, manual copy of
// just the theme), sync once on the next admin request instead of showing a
// broken site.
add_action('admin_init', function (): void {
    if (defined('SK_VERSION')) {
        return;
    }
    if (get_option('sk_auto_sync_blocked')) {
        return;
    }
    if (!sk_sync()) {
        update_option('sk_auto_sync_blocked', 1, false);
    }
});

/* ---------------------------------------------------------------------------
 * 2) GitHub updater (plugin-update-checker 5.7, branch mode)
 *
 * Settings are read from the shipped config file so this bootstrap stays
 * generic. The token itself must never be in the repository — it is read from
 * a wp-config.php constant (name is configurable per project):
 *
 *     define('SK_GITHUB_TOKEN', 'github_pat_…');
 *
 * Required scope for a private repository: Contents → Read.
 * ------------------------------------------------------------------------ */

/**
 * Minimal, dependency-free read of the updater settings.
 *
 * This runs before the mu-plugin is loaded, so it cannot use the plugin's
 * Config class. It reads the same config.php file directly.
 *
 * @return array{enabled: bool, repo: string, branch: string, token: string}
 */
function sk_updater_settings(): array
{
    static $settings = null;

    if (is_array($settings)) {
        return $settings;
    }

    $defaults = [
        'enabled'        => true,
        'repo'           => '',
        'branch'         => 'main',
        'token_constant' => 'SK_GITHUB_TOKEN',
        'token_env'      => 'SK_GITHUB_TOKEN',
    ];

    $config_file = sk_sync_source_dir() . 'site-kit/config.php';
    $config      = [];

    if (is_readable($config_file)) {
        $loaded = require $config_file;
        if (is_array($loaded)) {
            $config = $loaded;
        }
    }

    $updater = array_replace($defaults, (array) ($config['updater'] ?? []));

    if (defined('SK_GITHUB_REPO') && SK_GITHUB_REPO) {
        $updater['repo'] = (string) SK_GITHUB_REPO;
    }
    if (defined('SK_GITHUB_BRANCH') && SK_GITHUB_BRANCH) {
        $updater['branch'] = (string) SK_GITHUB_BRANCH;
    }

    $token = '';
    $name  = (string) $updater['token_constant'];
    if ($name !== '' && defined($name) && constant($name)) {
        $token = (string) constant($name);
    } elseif (!empty($updater['token_env']) && getenv((string) $updater['token_env'])) {
        $token = (string) getenv((string) $updater['token_env']);
    }

    $repo = trim((string) $updater['repo']);

    $settings = [
        'enabled'    => (bool) $updater['enabled'],
        'repo'       => $repo,
        'branch'     => trim((string) $updater['branch']) ?: 'main',
        'token'      => $token,
        'configured' => (bool) preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)
            && strpos($repo, 'your-github-user') !== 0,
    ];

    return $settings;
}

if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')
    && file_exists(__DIR__ . '/inc/plugin-update-checker/plugin-update-checker.php')
) {
    require_once __DIR__ . '/inc/plugin-update-checker/plugin-update-checker.php';
}

if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    $sk_updater = sk_updater_settings();

    if ($sk_updater['enabled'] && $sk_updater['configured']) {
        // The installed folder name is not stable (ZIP uploads get a suffix),
        // so derive the slug from the actual theme directory.
        $sk_theme_slug = basename(get_template_directory());

        $sk_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/' . $sk_updater['repo'] . '/',
            __FILE__,
            $sk_theme_slug
        );

        if (method_exists($sk_checker, 'setBranch')) {
            $sk_checker->setBranch($sk_updater['branch']);
        }

        if ($sk_updater['token'] !== '' && method_exists($sk_checker, 'setAuthentication')) {
            $sk_checker->setAuthentication($sk_updater['token']);
        }
    }
}

/* ---------------------------------------------------------------------------
 * 3) Admin notices: make a broken update setup visible instead of silent
 * ------------------------------------------------------------------------ */

add_action('admin_notices', function (): void {
    if (!current_user_can('update_themes')) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $allowed = ['themes', 'theme-install', 'dashboard', 'update-core', 'tools_page_site-kit'];
    if (!$screen instanceof WP_Screen || !in_array($screen->id, $allowed, true)) {
        return;
    }

    $updater = sk_updater_settings();

    if (!$updater['enabled']) {
        return;
    }

    if (!$updater['configured']) {
        printf(
            '<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
            esc_html__('Site Kit:', 'site-kit'),
            wp_kses_post(sprintf(
                /* translators: %s: config file path. */
                __('Theme updates are off because no GitHub repository is configured. Set <code>updater.repo</code> to <code>owner/repository</code> in <code>%s</code>.', 'site-kit'),
                'site/site-kit/config.php'
            ))
        );
        return;
    }

    if ($updater['token'] === '' && !sk_repo_is_public($updater['repo'])) {
        printf(
            '<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
            esc_html__('Site Kit:', 'site-kit'),
            wp_kses_post(sprintf(
                /* translators: 1: constant name, 2: repository. */
                __('The repository <code>%2$s</code> is private or unreachable, so WordPress cannot check for updates. Add <code>define(\'%1$s\', \'github_pat_…\');</code> to <code>wp-config.php</code>, or make the repository public.', 'site-kit'),
                esc_html((string) apply_filters('sk_token_constant_name', 'SK_GITHUB_TOKEN')),
                esc_html($updater['repo'])
            ))
        );
    }
});

/**
 * Probe whether a repository is anonymously readable (cached for an hour).
 *
 * @param string $repo "owner/name".
 */
function sk_repo_is_public(string $repo): bool
{
    $key    = 'sk_repo_public_' . md5($repo);
    $cached = get_transient($key);

    if ($cached !== false) {
        return $cached === 'yes';
    }

    $response = wp_remote_get('https://api.github.com/repos/' . $repo, [
        'timeout' => 8,
        'headers' => ['Accept' => 'application/vnd.github+json'],
    ]);

    $public = (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200);

    set_transient($key, $public ? 'yes' : 'no', HOUR_IN_SECONDS);

    return $public;
}
