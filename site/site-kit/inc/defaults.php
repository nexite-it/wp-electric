<?php
/**
 * Defaults for everything configurable.
 *
 * config.php is merged over this array (recursively), so a project only has to
 * declare what it changes. Keeping the defaults here also means the plugin can
 * boot with an empty or partially filled config.php.
 */

defined('ABSPATH') || exit;

return [

    /* Meta keys used by the templates. Renaming them per project is allowed but
       not necessary — they are namespaced and never collide with plugins. */
    'meta_keys' => [
        'event_date'         => 'sk_event_date',
        'event_time'         => 'sk_event_time',
        'event_location'     => 'sk_event_location',
        'event_registration' => 'sk_event_registration',
        'document_file'      => 'sk_document_file',
        'document_date'      => 'sk_document_date',
        'person_role'        => 'sk_person_role',
        'person_group'       => 'sk_person_group',
        'person_email'       => 'sk_person_email',
    ],

    /* Local-business blocks. Everything is optional: with these defaults the
       emergency line, opening hours, partner strip and service-area lists
       simply do not render. */
    'emergency' => [
        'enabled' => false,
        'phone'   => '',
        'label'   => 'Notdienst',
        'note'    => '',
        'eta'     => '',
    ],
    'hours' => [
        'timezone' => 'Europe/Berlin',
        'week'     => ['mo' => '', 'tu' => '', 'we' => '', 'th' => '', 'fr' => '', 'sa' => '', 'su' => ''],
    ],
    'service_area' => [],
    'partners'     => [],
    'legal'        => [
        'managing_director' => '',
        'register'          => '',
        'vat_id'            => '',
        'profession'        => '',
        'chamber'           => '',
        'content_owner'     => '',
    ],

    /* ---------------------------------------------------------------------
     * Content model
     *
     * Declarative: post types, taxonomies and meta fields are arrays. The
     * plugin registers them, and meta boxes are generated from the field
     * definitions — no hand-written HTML per field.
     *
     * field keys:
     *   key      meta key (required)
     *   label    admin label
     *   type     text | textarea | date | time | url | email | number | select | checkbox
     *   box      meta box title (fields with the same box share one box)
     *   options  for type=select: value => label
     *   help     small hint under the field
     *   width    'full' (default) or 'half'
     *   default  default value for new posts
     * ------------------------------------------------------------------- */
    'content' => [

        'post_types' => [
            'event' => [
                'labels' => [
                    'name'          => 'Events',
                    'singular_name' => 'Event',
                    'add_new_item'  => 'Add event',
                    'edit_item'     => 'Edit event',
                    'all_items'     => 'All events',
                    'not_found'     => 'No events yet',
                ],
                'description'   => 'Dated events with location and optional registration.',
                'icon'          => 'dashicons-calendar-alt',
                'menu_position' => 21,
                'supports'      => ['title', 'editor', 'excerpt', 'thumbnail'],
                'has_archive'   => true,
                'rewrite'       => 'events',
                'taxonomies'    => ['event_type'],
            ],
            'news' => [
                'labels' => [
                    'name'          => 'News',
                    'singular_name' => 'News item',
                    'add_new_item'  => 'Add news item',
                    'edit_item'     => 'Edit news item',
                    'all_items'     => 'All news',
                    'not_found'     => 'No news yet',
                ],
                'description'   => 'Short updates and announcements.',
                'icon'          => 'dashicons-megaphone',
                'menu_position' => 22,
                'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'author'],
                'has_archive'   => true,
                'rewrite'       => 'news',
            ],
            'document' => [
                'labels' => [
                    'name'          => 'Documents',
                    'singular_name' => 'Document',
                    'add_new_item'  => 'Add document',
                    'edit_item'     => 'Edit document',
                    'all_items'     => 'All documents',
                    'not_found'     => 'No documents yet',
                ],
                'description'   => 'Downloads and published files.',
                'icon'          => 'dashicons-media-document',
                'menu_position' => 23,
                'supports'      => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
                'has_archive'   => true,
                'rewrite'       => 'documents',
                'taxonomies'    => ['document_type'],
            ],
            'person' => [
                'labels' => [
                    'name'          => 'People',
                    'singular_name' => 'Person',
                    'add_new_item'  => 'Add person',
                    'edit_item'     => 'Edit person',
                    'all_items'     => 'All people',
                    'not_found'     => 'No people yet',
                ],
                'description'   => 'Team members grouped by role.',
                'icon'          => 'dashicons-groups',
                'menu_position' => 24,
                'supports'      => ['title', 'editor', 'thumbnail', 'page-attributes'],
                'has_archive'   => false,
                'public'        => true,
                'rewrite'       => 'people',
            ],
        ],

        'taxonomies' => [
            'event_type' => [
                'labels' => [
                    'name'          => 'Event types',
                    'singular_name' => 'Event type',
                ],
                'post_types'   => ['event'],
                'hierarchical' => false,
                'rewrite'      => 'event-type',
            ],
            'document_type' => [
                'labels' => [
                    'name'          => 'Document types',
                    'singular_name' => 'Document type',
                ],
                'post_types'   => ['document'],
                'hierarchical' => true,
                'rewrite'      => 'document-type',
            ],
        ],

        // Field definitions per post type. Meta boxes are generated from `box`.
        'meta' => [
            'event' => [
                ['key' => 'sk_event_date', 'label' => 'Date', 'type' => 'date', 'box' => 'Event details', 'width' => 'half'],
                ['key' => 'sk_event_time', 'label' => 'Start time', 'type' => 'time', 'box' => 'Event details', 'width' => 'half', 'help' => 'e.g. 19:00'],
                ['key' => 'sk_event_location', 'label' => 'Location', 'type' => 'text', 'box' => 'Event details'],
                ['key' => 'sk_event_registration', 'label' => 'Registration required', 'type' => 'checkbox', 'box' => 'Event details', 'width' => 'half'],
            ],
            'document' => [
                ['key' => 'sk_document_file', 'label' => 'File or link (URL)', 'type' => 'url', 'box' => 'Document details', 'help' => 'Upload the file in the media library, then paste its URL here.'],
                ['key' => 'sk_document_date', 'label' => 'Date', 'type' => 'date', 'box' => 'Document details', 'width' => 'half'],
            ],
            'person' => [
                ['key' => 'sk_person_role', 'label' => 'Role', 'type' => 'text', 'box' => 'Person details', 'width' => 'half'],
                [
                    'key'     => 'sk_person_group',
                    'label'   => 'Group',
                    'type'    => 'select',
                    'box'     => 'Person details',
                    'width'   => 'half',
                    'default' => 'team',
                    'options' => ['team' => 'Team', 'board' => 'Board', 'alumni' => 'Alumni'],
                ],
                ['key' => 'sk_person_email', 'label' => 'E-mail', 'type' => 'email', 'box' => 'Person details', 'width' => 'half'],
            ],
        ],
    ],

    /* Pages created on first run, in order. `template` refers to a file in
       templates/ without the prefix. `front` marks the front page.

       Note: events, news and documents deliberately have no page here — their
       post type archives own those URLs (/events/, /news/, /documents/). Two
       things claiming the same slug is the one reliable way to break permalinks,
       so the kit does not create it. */
    'pages' => [
        ['slug' => 'home',    'title' => 'Home',    'template' => 'start', 'front' => true],
        ['slug' => 'about',   'title' => 'About',   'template' => 'about'],
        ['slug' => 'team',    'title' => 'Team',    'template' => 'team'],
        ['slug' => 'contact', 'title' => 'Contact', 'template' => 'contact'],
        ['slug' => 'imprint', 'title' => 'Imprint', 'template' => 'imprint'],
        ['slug' => 'privacy', 'title' => 'Privacy', 'template' => 'privacy'],
    ],

    /* Menu fallback when no menu is assigned: config.php `nav` + this footer set. */
    'footer_nav' => [
        ['label' => 'About',   'slug' => 'about'],
        ['label' => 'Contact', 'slug' => 'contact'],
        ['label' => 'Imprint', 'slug' => 'imprint'],
        ['label' => 'Privacy', 'slug' => 'privacy'],
    ],
];
