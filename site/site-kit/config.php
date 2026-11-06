<?php
/**
 * Site configuration — Elektro Wendt, Elektro-Meisterbetrieb in Bonn.
 *
 * This file is pure data: it is read by the carrier theme (update checker) and
 * by the mu-plugin (templates, SEO, forms). Nothing here touches the database,
 * so whatever you commit is what every environment gets.
 *
 * Business data (name, phone numbers, hours, service area, legal details)
 * lives here. Page copy (service descriptions, FAQ, values, jobs) lives in
 * inc/content.php. Every value below is a placeholder — replace before launch.
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
     * `founded` drives the "Jahre Erfahrung" counter, so it never goes stale.
     * `colors` are injected as CSS variables (see assets/css/main.css):
     *   primary       deep slate — headings, dark panels
     *   primary_dark  near-black navy — hero, footer, top bar
     *   accent        safety orange — calls to action (dark text on top)
     *   accent_dark   accent hover
     *   link          electric blue — inline links and focus rings
     * ------------------------------------------------------------------- */
    'brand' => [
        'name'        => 'Elektro Wendt',
        'short'       => 'Elektro Wendt',
        'legal_name'  => 'Elektro Wendt GmbH',
        'tagline'     => 'Elektro-Meisterbetrieb in Bonn',
        'description' => 'Ihr Elektro-Meisterbetrieb in Bonn und im Rhein-Sieg-Kreis: Wallbox-Installation, PV-Anschluss, Smart Home, Altbausanierung und 24/7-Notdienst.',
        'founded'     => 1998,
        'colors'      => [
            'primary'      => '#13213a',
            'primary_dark' => '#0b1424',
            'accent'       => '#ff6b1a',
            'accent_dark'  => '#f25a08',
            'link'         => '#1f6feb',
        ],
    ],

    /* ---------------------------------------------------------------------
     * 3) Contact details
     *
     * `recipient` receives every form submission. Leave it empty while the
     * placeholder e-mail is still in place: submissions then go to the
     * WordPress admin e-mail instead of an address that does not exist yet.
     *
     * `whatsapp` is the number shown on the site (any format); the wa.me link
     * is built from its digits. Leave it empty to hide every WhatsApp button.
     *
     * `map` is an OpenStreetMap bounding box ("left,bottom,right,top"). The
     * map is only loaded after a click (two-click solution, GDPR-friendly);
     * leave it empty to show the stylised service-area map only.
     * ------------------------------------------------------------------- */
    'contact' => [
        'street'        => 'Musterstraße 12',
        'city'          => '53111 Bonn',
        'country'       => 'Deutschland',
        'phone'         => '0228 123 456-0',
        'email'         => 'info@elektro-wendt.de',
        'recipient'     => '',
        'whatsapp'      => '0170 123 45 67',
        'whatsapp_text' => 'Hallo Elektro Wendt, ich habe eine Frage zu folgendem Projekt:',
        'map'           => '6.9000,50.6000,7.4000,50.8800',
        'map_label'     => 'Karte des Einsatzgebiets Bonn und Rhein-Sieg-Kreis',
    ],

    /* ---------------------------------------------------------------------
     * 4) Emergency service (Notdienst)
     *
     * Shown in the header, hero, mobile call bar, contact page and footer.
     * Set `enabled` to false (or leave `phone` empty) to remove it everywhere.
     * ------------------------------------------------------------------- */
    'emergency' => [
        'enabled' => true,
        'phone'   => '0228 123 456-99',
        'label'   => 'Notdienst 24/7',
        'note'    => 'Stromausfall, Kurzschluss oder Brandgeruch? Rufen Sie direkt an – wir sind rund um die Uhr erreichbar, auch an Wochenenden und Feiertagen.',
        'eta'     => 'Im Bonner Stadtgebiet in der Regel innerhalb von 60 Minuten vor Ort.',
    ],

    /* ---------------------------------------------------------------------
     * 5) Opening hours (office)
     *
     * One entry per weekday, "HH:MM-HH:MM" or '' for closed. The site groups
     * equal days for display ("Mo – Do"), shows a live "jetzt geöffnet" state
     * and publishes the hours as structured data for Google.
     * ------------------------------------------------------------------- */
    'hours' => [
        'timezone' => 'Europe/Berlin',
        'week'     => [
            'mo' => '07:00-17:00',
            'tu' => '07:00-17:00',
            'we' => '07:00-17:00',
            'th' => '07:00-17:00',
            'fr' => '07:00-14:00',
            'sa' => '',
            'su' => '',
        ],
    ],

    /* Towns listed on the about and contact pages and in structured data. */
    'service_area' => [
        'Bonn', 'Bad Godesberg', 'Beuel', 'Hardtberg', 'Sankt Augustin', 'Siegburg',
        'Troisdorf', 'Niederkassel', 'Königswinter', 'Bad Honnef', 'Hennef',
        'Bornheim', 'Alfter', 'Meckenheim', 'Wachtberg', 'Rheinbach',
    ],

    /* ---------------------------------------------------------------------
     * 6) Partner brands (trust bar)
     *
     * Without `logo` the name is set as a neutral wordmark. To show real
     * logos, put the files you are licensed to use (as an authorised partner)
     * into assets/img/partners/ and set e.g. 'logo' => 'partners/gira.svg'.
     * ------------------------------------------------------------------- */
    'partners' => [
        ['name' => 'Gira',         'logo' => ''],
        ['name' => 'Hager',        'logo' => ''],
        ['name' => 'Busch-Jaeger', 'logo' => ''],
        ['name' => 'Mennekes',     'logo' => ''],
        ['name' => 'Jung',         'logo' => ''],
        ['name' => 'SMA',          'logo' => ''],
    ],

    /* ---------------------------------------------------------------------
     * 7) Legal details for the Impressum (§ 5 DDG, DL-InfoV)
     * ------------------------------------------------------------------- */
    'legal' => [
        'managing_director' => 'Markus Wendt',
        'register'          => 'Amtsgericht Bonn, HRB 00000',
        'vat_id'            => 'DE000000000',
        'profession'        => 'Elektrotechnikermeister (verliehen in der Bundesrepublik Deutschland)',
        'chamber'           => 'Handwerkskammer zu Köln, Heumarkt 12, 50667 Köln',
        'content_owner'     => 'Markus Wendt',
    ],

    /* ---------------------------------------------------------------------
     * 8) Social links
     * Available: instagram, facebook, linkedin, x, youtube, github, mail, rss.
     * ------------------------------------------------------------------- */
    'social' => [
        'instagram' => '',
        'facebook'  => '',
        'linkedin'  => '',
    ],

    /* ---------------------------------------------------------------------
     * 9) Navigation
     *
     * `slug` is a page slug (see `pages` below). `cta` is the highlighted
     * button in the header.
     * ------------------------------------------------------------------- */
    'nav' => [
        ['label' => 'Startseite', 'slug' => 'home'],
        ['label' => 'Leistungen', 'slug' => 'leistungen'],
        ['label' => 'Über uns',   'slug' => 'ueber-uns'],
        ['label' => 'Kontakt',    'slug' => 'kontakt'],
    ],
    'cta' => ['label' => 'Projekt anfragen', 'slug' => 'kontakt'],

    'footer_nav' => [
        ['label' => 'Impressum',   'slug' => 'impressum'],
        ['label' => 'Datenschutz', 'slug' => 'datenschutz'],
    ],

    /* ---------------------------------------------------------------------
     * 10) Features
     *
     * The demo post types of the kit (events, news, documents, people) are
     * switched off below, so there is no demo content to seed.
     * ------------------------------------------------------------------- */
    'features' => [
        'seed_demo_content' => false,
        'seed_images'       => false,
        'rsvp'              => false,
        'search'            => false,
    ],

    /* ---------------------------------------------------------------------
     * 11) SEO defaults
     * ------------------------------------------------------------------- */
    'seo' => [
        'locale'          => 'de_DE',
        'og_image'        => 'assets/img/site/og-default.png',
        'twitter_card'    => 'summary_large_image',
        'title_separator' => '–',
    ],

    /* ---------------------------------------------------------------------
     * 12) Content model
     *
     * `false` switches a default post type or taxonomy off. Remove a line to
     * bring it back (e.g. 'news' for a "Referenzen" or "Aktuelles" section).
     * ------------------------------------------------------------------- */
    'content' => [
        'post_types' => [
            'event'    => false,
            'news'     => false,
            'document' => false,
            'person'   => false,
        ],
        'taxonomies' => [
            'event_type'    => false,
            'document_type' => false,
        ],
    ],

    /* ---------------------------------------------------------------------
     * 13) Pages created on first run (in this order)
     *
     * `template` names a file in templates/ (template-<name>.php). Templates
     * link to each other by template name, so slugs can change here freely.
     * ------------------------------------------------------------------- */
    'pages' => [
        ['slug' => 'home',        'title' => 'Startseite',  'template' => 'start', 'front' => true],
        ['slug' => 'leistungen',  'title' => 'Leistungen',  'template' => 'services'],
        ['slug' => 'ueber-uns',   'title' => 'Über uns',    'template' => 'about'],
        ['slug' => 'kontakt',     'title' => 'Kontakt',     'template' => 'contact'],
        ['slug' => 'impressum',   'title' => 'Impressum',   'template' => 'imprint'],
        ['slug' => 'datenschutz', 'title' => 'Datenschutz', 'template' => 'privacy'],
    ],
];
