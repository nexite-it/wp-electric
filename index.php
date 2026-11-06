<?php
/**
 * Index fallback for the carrier theme.
 *
 * Every public view is rendered by the Site Kit mu-plugin through
 * template_include. This file only exists because WordPress requires index.php
 * in a theme, and it points at the plugin's 404 template if the plugin is
 * somehow not loaded yet (for example directly after a theme switch, before
 * the sync ran).
 */

defined('ABSPATH') || exit;

if (defined('SK_DIR') && function_exists('SiteKit\\Template\\partial')) {
    $template = \SiteKit\Template\tpl('template-404.php');
    if ($template !== '') {
        require $template;
        return;
    }
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php bloginfo('name'); ?></title>
</head>
<body style="font-family:system-ui,sans-serif;padding:64px;text-align:center;background:#f7f7f7;color:#222">
    <h1 style="margin:0 0 12px"><?php bloginfo('name'); ?></h1>
    <p style="margin:0;color:#666"><?php esc_html_e('The site is being set up. Please reload this page in a moment.', 'site-kit'); ?></p>
</body>
</html>
