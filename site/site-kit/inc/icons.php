<?php
/**
 * Inline SVG icon set (Lucide-style strokes) — local, no CDN.
 *
 * Usage: SiteKit\Icons\get('name'), SiteKit\Icons\out('name', 'class')
 * An unknown name falls back to 'sparkles' so templates never render empty.
 */

namespace SiteKit\Icons;

defined('ABSPATH') || exit;

const PATHS = [
    'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'arrow-up'      => '<path d="M12 19V5M6 11l6-6 6 6"/>',
    'arrow-left'    => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
    'check'         => '<path d="M20 6 9 17l-5-5"/>',
    'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
    'menu'          => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
    'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
    'location'      => '<path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
    'calendar'      => '<rect x="3" y="4.5" width="18" height="16" rx="3"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
    'clock'         => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    'mail'          => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m4 7 8 6 8-6"/>',
    'phone'         => '<path d="M6 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L15 12l5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 4 5a2 2 0 0 1 2-2z"/>',
    'users'         => '<circle cx="9" cy="8" r="3.5"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M15.5 8a3.5 3.5 0 1 1 3 5.5"/><path d="M13.5 20a5.5 5.5 0 0 1 7-5"/>',
    'user-plus'     => '<circle cx="10" cy="8" r="3.5"/><path d="M4 20a6 6 0 0 1 12 0M19 8v6M16 11h6"/>',
    'landmark'      => '<path d="M4 21h16M5 18h14M6 18v-7M10 18v-7M14 18v-7M18 18v-7M3 11l9-6 9 6"/>',
    'graduation'    => '<path d="m3 9 9-5 9 5-9 5-9-5z"/><path d="M7 12v4.5c0 1.5 2 2.5 5 2.5s5-1 5-2.5V12M21 9v5"/>',
    'monitor'       => '<rect x="3" y="4" width="18" height="13" rx="2.5"/><path d="M9 21h6M12 17.5V21"/>',
    'coffee'        => '<path d="M4 8h12v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8z"/><path d="M16 9h2.5a2.5 2.5 0 0 1 0 5H16M4 21h13"/>',
    'briefcase'     => '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7M3 12h18"/>',
    'heart'         => '<path d="M12 20.5S4 15 4 9.5A4.5 4.5 0 0 1 12 6.6a4.5 4.5 0 0 1 8 2.9c0 5.5-8 11-8 11z"/>',
    'star'          => '<path d="m12 3.5 2.6 5.4 5.9.8-4.3 4.2 1 5.9-5.2-2.8-5.2 2.8 1-5.9L3.5 9.7l5.9-.8z"/>',
    'sparkles'      => '<path d="M12 4v4M12 16v4M4 12h4M16 12h4"/><path d="m6.8 6.8 2 2M15.2 15.2l2 2M17.2 6.8l-2 2M8.8 15.2l-2 2"/>',
    'megaphone'     => '<path d="m3 11 15-6v14L3 13v-2z"/><path d="M7 13v5a2 2 0 0 0 4 0v-3.5M3 11v2"/>',
    'instagram'     => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.5" fill="currentColor"/>',
    'facebook'      => '<path d="M14 8h2.5V4.5H14a3.5 3.5 0 0 0-3.5 3.5v2H8V13h2.5v7h3.5v-7H16l.5-3h-2.5V8z"/>',
    'linkedin'      => '<path d="M5 9v10M5 5.5v0M9 19v-6.5a3 3 0 0 1 6 0V19M9 10v3"/>',
    'x'             => '<path d="M4 4l16 16M20 4 4 20"/>',
    'youtube'       => '<rect x="2.5" y="6" width="19" height="12" rx="3.5"/><path d="m10.5 9.5 5 2.5-5 2.5z"/>',
    'github'        => '<path d="M9 20v-3c-3 .7-4-1.5-4-1.5M15 20v-3.2a2.8 2.8 0 0 0-.8-2.2c2.6-.3 4.3-1.4 4.3-4.6a3.6 3.6 0 0 0-1-2.5 3.3 3.3 0 0 0-.1-2.5s-.9-.3-2.9 1a10 10 0 0 0-5 0c-2-1.3-2.9-1-2.9-1a3.3 3.3 0 0 0-.1 2.5 3.6 3.6 0 0 0-1 2.5c0 3.2 1.7 4.3 4.3 4.6A2.8 2.8 0 0 0 9 15.7V20"/>',
    'rss'           => '<path d="M4 11a9 9 0 0 1 9 9M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1.5" fill="currentColor" stroke="none"/>',
    'download'      => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
    'external'      => '<path d="M14 4h6v6M20 4 11 13"/><path d="M20 14v4a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18v-11A2.5 2.5 0 0 1 6.5 4h4"/>',
    'doc'           => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 13h6M9 16.5h6"/>',
    'banknote'      => '<rect x="3" y="7" width="18" height="11" rx="2.5"/><circle cx="12" cy="12.5" r="2.6"/><path d="M6.5 10v.01M17.5 15v.01"/>',
    'network'       => '<circle cx="5.5" cy="12" r="2.5"/><circle cx="18.5" cy="5.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/><path d="M8 11l8-4M8 13l8 4"/>',
    'handshake'     => '<path d="m4 12 2.5-2.5 3 3 3-3 3 3L16.5 9M4 12v3.5a1.5 1.5 0 0 0 1.5 1.5h1l2-2M20 12v3.5a1.5 1.5 0 0 1-1.5 1.5h-1l-2-2"/>',
    'gift'          => '<rect x="4" y="9" width="16" height="4" rx="1.5"/><path d="M5.5 13v7h13v-7M12 9v11M12 9C9 9 7 8 7 6.5A2 2 0 0 1 9 4.5c1.5 0 3 1.8 3 4.5zM12 9c3 0 5-1 5-2.5a2 2 0 0 0-2-2c-1.5 0-3 1.8-3 4.5z"/>',
    'book'          => '<path d="M12 6.5C10 4.5 7 4.2 4 5v14c3-.8 6-.5 8 1.5 2-2 5-2.3 8-1.5V5c-3-.8-6-.5-8 1.5z"/><path d="M12 6.5v16"/>',
    'search'        => '<circle cx="11" cy="11" r="7"/><path d="m16.2 16.2 4.8 4.8"/>',
    'tag'           => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
    'file-text'     => '<path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
    'shield'        => '<path d="M12 3 5 6v6c0 4.2 2.9 7.7 7 9 4.1-1.3 7-4.8 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
    'zap'           => '<path d="M13 3 5 14h6l-1 7 8-11h-6z"/>',

    // Trade icons (Elektro): drawn for this set, same 24px grid and stroke.
    'ev'            => '<path d="M4 21V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v16M2.5 21h14"/><path d="m10.5 7-2.5 4h3.5L9 15"/><path d="M15 10h1.5a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 0 3 0V8l-2.5-2.5"/>',
    'smart-home'    => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9v12h14V9"/><path d="M8.8 14.6a4.5 4.5 0 0 1 6.4 0"/><circle cx="12" cy="17.6" r=".7" fill="currentColor"/>',
    'building'      => '<path d="M3 21h18M5 21V8.5L12 4l7 4.5V21"/><path d="M10 21v-4.5h4V21"/><path d="M8.5 10h2v2h-2zM13.5 10h2v2h-2z"/>',
    'wrench'        => '<path d="M19.7 6.14 16.63 9.21 14.79 7.37 17.86 4.3A5.5 5.5 0 0 0 10.2 11.68L4.75 17.13A1.5 1.5 0 0 0 6.87 19.25L12.32 13.8A5.5 5.5 0 0 0 19.7 6.14Z"/>',
    'solar'         => '<path d="M6 12h15l-2.5 8H3.5z"/><path d="M11 12 8.5 20M16 12l-2.5 8M4.75 16h15"/><path d="M12.5 3v3M7.5 5 9 7M17.5 5 16 7"/>',
    'whatsapp'      => '<path d="M3.5 20.5 5.07 15.5A8 8 0 1 1 8.62 18.75Z"/><path d="M9.6 8.6c-.4 2.5 2.4 5.5 5 5.7l.8-1.4-1.7-1-.8.6a3.8 3.8 0 0 1-1.9-1.9l.6-.8-1-1.7z"/>',
    'award'         => '<circle cx="12" cy="9" r="6"/><path d="M8.6 13.9 7 21l5-2.8 5 2.8-1.6-7.1"/>',
    'clipboard'     => '<rect x="5" y="4.5" width="14" height="17" rx="2"/><path d="M9 4.5v-1a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1M9 13l2 2 4-4"/>',
    'plug'          => '<path d="M9 2.5V7M15 2.5V7M6.5 7h11v3.5a5.5 5.5 0 0 1-11 0zM12 16v5.5"/>',
    'helmet'        => '<path d="M2 18.5h20"/><path d="M4 18.5V16a8 8 0 0 1 16 0v2.5"/><path d="M9.5 8.6V14M14.5 8.6V14"/>',
    'eye'           => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    'alert'         => '<path d="M10.3 4.2 2.6 17.5a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0z"/><path d="M12 9.5v4M12 17h.01"/>',
    'euro'          => '<path d="M17.5 6.2A7.3 7.3 0 1 0 17.5 17.8M4 10.2h9M4 13.8h9"/>',
];

/** Inline SVG markup for an icon. */
function get(string $name, string $class = 'sk-icon'): string
{
    $path = PATHS[$name] ?? PATHS['sparkles'];
    $cls  = $class !== '' ? ' class="' . esc_attr($class) . '"' : '';

    // width/height attributes: without them an inline SVG falls back to the
    // 300×150 default size in browsers that have no CSS for it. CSS may
    // override these — the attributes are only a safe 1em default.
    return '<svg' . $cls . ' width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $path . '</svg>';
}

/** Echo an icon. */
function out(string $name, string $class = 'sk-icon'): void
{
    echo get($name, $class); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built internally
}

/** Whether an icon name exists. */
function exists(string $name): bool
{
    return isset(PATHS[$name]);
}

/** All icon names (used by the demo content / tooling). */
function names(): array
{
    return array_keys(PATHS);
}

/**
 * Map a social network key to an icon name.
 *
 * @param string $network
 */
function social_icon(string $network): string
{
    $map = [
        'instagram' => 'instagram',
        'facebook'  => 'facebook',
        'linkedin'  => 'linkedin',
        'x'         => 'x',
        'twitter'   => 'x',
        'youtube'   => 'youtube',
        'github'    => 'github',
        'rss'       => 'rss',
        'mail'      => 'mail',
    ];

    return $map[$network] ?? 'external';
}
