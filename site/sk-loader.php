<?php
/**
 * mu-plugin loader.
 *
 * WordPress only auto-loads single files that sit directly in
 * wp-content/mu-plugins/ — sub-directories are ignored. This loader is copied
 * there by the theme (see functions.php, sk_site_sync()) and includes the real
 * plugin from the site-kit/ sub-directory.
 */

defined('ABSPATH') || exit;

$sk_plugin = trailingslashit(WPMU_PLUGIN_DIR) . 'site-kit/site-kit.php';

if (is_readable($sk_plugin)) {
    require_once $sk_plugin;
}
