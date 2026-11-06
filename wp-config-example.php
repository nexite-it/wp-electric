<?php
/**
 * Example: what to add to the host's wp-config.php for this template.
 *
 * This file is documentation, not part of the theme. It is never loaded by
 * WordPress — copy the lines you need into the REAL wp-config.php on the
 * server, directly ABOVE the line that says "That's all, stop editing!".
 *
 * Never commit a real token. Keep this file as a reminder only; the matching
 * .gitignore entry already excludes real wp-config files.
 *
 * @package SiteKit
 */

/* ---------------------------------------------------------------------------
 * 1) Site Kit: GitHub updates for the carrier theme.
 *
 * Create ONE fine-grained personal access token per project repository:
 *
 *   GitHub -> Settings -> Developer settings -> Fine-grained tokens
 *          -> Generate new token
 *   Repository access: Only select repositories -> <owner>/<repo>
 *   Permissions:       Contents -> Read-only
 *
 * Then paste the token below, on the host only. The repository itself stays
 * private; the token lives exclusively in wp-config.php.
 * ------------------------------------------------------------------------ */

define('SK_GITHUB_TOKEN', 'github_pat_COPY_YOUR_TOKEN_HERE');

/* Optional: per-environment overrides. Useful when the same code runs on a
 * staging host that should track a different branch or a fork.
 *
 * The constant names can be changed per project from
 * site/site-kit/config.php -> updater.token_constant.
 *
 * define('SK_GITHUB_REPO', 'your-user/your-repo');
 * define('SK_GITHUB_BRANCH', 'main');
 */

/* ---------------------------------------------------------------------------
 * 2) Site Kit: recommended WordPress settings on managed hosts.
 * ------------------------------------------------------------------------ */

/* When something breaks on the host, enable these temporarily to get
 * wp-content/debug.log, then remove them again.
 *
 * define('WP_DEBUG', true);
 * define('WP_DEBUG_LOG', true);
 * define('WP_DEBUG_DISPLAY', false);
 */

/* The site works with any permalink structure, but the seeded pages assume
 * pretty URLs; the seeder sets /%postname%/ on first run anyway. Nothing to
 * add here on most hosts. */
