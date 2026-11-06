<?php
/**
 * Plugin Name: Site Kit
 * Description: Theme-independent site core: templates, post types, seeding, forms and design system. Shipped by the carrier theme and synced to wp-content/mu-plugins/.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Your Organization
 * License: GPL-2.0-or-later
 * Text Domain: site-kit
 *
 * Architecture
 * ------------
 * The repository root is a WordPress theme whose only jobs are to be installable
 * and to update itself from GitHub. The actual site lives here, as an
 * mu-plugin, and is copied out of the theme's site/ folder on activation and on
 * every theme update (see the theme's functions.php).
 *
 *   inc/config.php    reads config.php — the only file you edit per project
 *   inc/setup.php     assets, <head>, theme supports, body classes
 *   inc/cpt.php       post types, taxonomies, meta fields and meta boxes
 *   inc/template.php  template resolution without a theme, partials, pagination
 *   inc/seed.php      page/menu/demo-content seeding, repair and admin tools
 *   inc/forms.php     contact and RSVP handling (nonce, honeypot, throttle)
 *   inc/icons.php     inline SVG icon set
 *   inc/util.php      output helpers used by the templates
 *   inc/content.php   demo content for fresh installs
 *
 * Nothing in here requires a specific theme, a page builder or a plugin.
 */

defined('ABSPATH') || exit;

define('SK_VERSION', '1.0.0');
define('SK_DIR', plugin_dir_path(__FILE__));
define('SK_FILE', __FILE__);

require_once SK_DIR . 'inc/util.php';
require_once SK_DIR . 'inc/config.php';
require_once SK_DIR . 'inc/icons.php';
require_once SK_DIR . 'inc/setup.php';
require_once SK_DIR . 'inc/cpt.php';
require_once SK_DIR . 'inc/template.php';
require_once SK_DIR . 'inc/content.php';
require_once SK_DIR . 'inc/seed.php';
require_once SK_DIR . 'inc/forms.php';

SiteKit\Setup\run();
SiteKit\CPT\run();
SiteKit\Template\register();
SiteKit\Seed\init();
SiteKit\Forms\init();
