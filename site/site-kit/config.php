<?php
/**
 * Site configuration — the single file you edit to make this project yours.
 *
 * This file is pure data: it is read by the carrier theme (update checker) and
 * by the mu-plugin (templates, SEO, forms). Nothing here touches the database,
 * so whatever you commit is what every environment gets.
 *
 * A new project is usually: copy the repo, edit this file, push.
 */

return [

    /* ---------------------------------------------------------------------
     * 1) GitHub updater
     *
     * The theme updates itself from a GitHub branch. Set `repo` and `branch`
     * once per project. `repo` must be "owner/name" — the URL is built from it.
     *
     * PRIVATE REPOSITORY: WordPress cannot read a private repo anonymously.
     * Create a fine-grained personal access token with "Contents: Read" and
     * put it in the host's wp-config.php (never in this repo):
     *
     *     define('SK_GITHUB_TOKEN', 'github_pat_...');
     *
     * `token_constant` is the name of that constant. Keep the default, or
     * change it per project to avoid collisions on shared setups.
     * ------------------------------------------------------------------- */
    'updater' => [
        'enabled'        => true,
        'repo'           => 'your-github-user/your-repo',
        'branch'         => 'main',
        'token_constant' => 'SK_GITHUB_TOKEN',
        'token_env'      => 'SK_GITHUB_TOKEN',
    ],

    /* ---------------------------------------------------------------------
     * 2) Brand
     *
     * `name` and `tagline` are the WordPress site title/description fallback
     * and appear in the header, footer and e-mails. `short` is used where the
     * full name is too long (mobile menu, footer bar).
     *
     * `colors` overrides the design tokens in assets/css/main.css. Set a value
     * to '' to keep the stylesheet default. Only these five keys are exposed;
     * everything else is derived from them.
     * ------------------------------------------------------------------- */
    'brand' => [
        'name'        => 'Your Organization',
        'short'       => 'Your Org',
        'legal_name'  => 'Your Organization e.V.',
        'tagline'     => 'A short, clear tagline.',
        'description' => 'One sentence that explains what this site is about. Used for SEO and as the hero lead.',
        'colors'      => [
            'primary'      => '#1d4265', // headings, header, footer, dark sections
            'primary_dark' => '#142f49', // depth (footer edge, hovers)
            'accent'       => '#ffa600', // buttons, markers, highlights
            'accent_dark'  => '#fe9700', // accent hover
            'link'         => '#0071b9', // inline links, focus rings
        ],
    ],

    /* ---------------------------------------------------------------------
     * 3) Contact details
     *
     * Printed on the contact page, in the footer and in the legal pages.
     * `recipient` receives every form submission (contact, RSVP).
     * `map` is an optional OpenStreetMap bounding box ("left,bottom,right,top");
     * leave it empty to render no map at all.
     * ------------------------------------------------------------------- */
    'contact' => [
        'street'    => 'Example Street 1',
        'city'      => '12345 Example City',
        'country'   => 'Germany',
        'phone'     => '+49 000 000000',
        'email'     => 'hello@example.com',
        'recipient' => 'hello@example.com',
        'map'       => '', // e.g. '7.0900,50.7200,7.1300,50.7500'
        'map_label' => '',
    ],

    /* ---------------------------------------------------------------------
     * 4) Social links
     *
     * Any network from the icon set can be listed; unknown keys are skipped.
     * Available: instagram, facebook, linkedin, x, youtube, github, mail, rss.
     * ------------------------------------------------------------------- */
    'social' => [
        'instagram' => '',
        'linkedin'  => '',
        'github'    => '',
    ],

    /* ---------------------------------------------------------------------
     * 5) Navigation
     *
     * The header navigation is defined here, in code, so it ships with the
     * repository (no click-ops after a deploy). `slug` may be a page slug or a
     * post type archive name: 'events' → /events/, 'news' → /news/.
     *
     * `cta` is the highlighted button on the right of the header.
     * ------------------------------------------------------------------- */
    'nav' => [
        ['label' => 'About',     'slug' => 'about'],
        ['label' => 'Team',      'slug' => 'team'],
        ['label' => 'Events',    'slug' => 'events'],
        ['label' => 'News',      'slug' => 'news'],
        ['label' => 'Documents', 'slug' => 'documents'],
    ],
    'cta' => ['label' => 'Contact', 'slug' => 'contact'],

    /* ---------------------------------------------------------------------
     * 6) Features
     *
     * `seed_demo_content` writes sample pages, menu and posts on first visit.
     * Turn it off for a truly empty install (pages are still created).
     * `seed_images` imports the bundled placeholder images into the media
     * library so the demo content has thumbnails.
     * ------------------------------------------------------------------- */
    'features' => [
        'seed_demo_content' => true,
        'seed_images'       => true,
        'rsvp'              => true,
        'search'            => true,
    ],

    /* ---------------------------------------------------------------------
     * 7) SEO defaults
     * ------------------------------------------------------------------- */
    'seo' => [
        'locale'          => 'en_US',
        'og_image'        => 'assets/img/site/og-default.png',
        'twitter_card'    => 'summary_large_image',
        'title_separator' => '·',
    ],

    /* ---------------------------------------------------------------------
     * 8) Content model
     *
     * Post types, taxonomies, meta fields and the pages created on first run
     * all come from inc/defaults.php. Override only what you need — the merge
     * is recursive, so this keeps every default you do not mention:
     *
     *   'content' => [
     *       'post_types' => [
     *           'event' => ['labels' => ['name' => 'Termine']],
     *       ],
     *   ],
     *
     * Add a whole new post type by declaring its full definition. Templates
     * pick it up automatically through the generic archive/single templates.
     * ------------------------------------------------------------------- */
    'content' => [],

    /* Override page list / footer nav the same way: */
    'pages'      => [],
    'footer_nav' => [],
];
