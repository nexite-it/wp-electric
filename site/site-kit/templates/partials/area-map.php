<?php
/**
 * Partial: stylised service-area map (Bonn & Rhein-Sieg-Kreis).
 *
 * A self-drawn SVG: Rhine, Sieg, distance rings around Bonn and the towns
 * from config.php → service_area. No third-party request happens until the
 * visitor clicks "Interaktive Karte laden" (only offered when contact.map is
 * set); main.js then swaps in the OpenStreetMap embed.
 *
 * Coordinates are projected from latitude/longitude around Bonn centre at
 * 11 px per km (viewBox 480 × 360), so the layout is geographically honest.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$contact = Config\contact();
$has_osm = $contact['map'] !== '' && preg_match('/^-?[\d.]+,-?[\d.]+,-?[\d.]+,-?[\d.]+$/', $contact['map']);
$osm_src = $has_osm ? 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($contact['map']) . '&layer=mapnik' : '';

// Town => [x, y, label position: l(eft), r(ight), t(op), b(ottom)].
// Only towns listed in config are drawn.
$positions = [
    'Bonn'           => [220, 190, 'l'],
    'Beuel'          => [240, 183, 't'],
    'Bad Godesberg'  => [263, 253, 'l'],
    'Hardtberg'      => [181, 214, 'l'],
    'Sankt Augustin' => [287, 147, 'b'],
    'Siegburg'       => [301, 111, 'r'],
    'Troisdorf'      => [263, 91, 'r'],
    'Niederkassel'   => [166, 92, 'l'],
    'Königswinter'   => [293, 265, 'r'],
    'Bad Honnef'     => [319, 300, 'r'],
    'Hennef'         => [363, 139, 'r'],
    'Bornheim'       => [134, 156, 'l'],
    'Alfter'         => [151, 190, 'l'],
    'Meckenheim'     => [166, 318, 'b'],
    'Wachtberg'      => [220, 319, 'r'],
    'Rheinbach'      => [103, 324, 'l'],
    'Lohmar'         => [312, 76, 'r'],
];
$towns = array_intersect_key($positions, array_flip(array_map('strval', (array) Config\get('service_area', []))));
if (!isset($towns['Bonn'])) {
    $towns = ['Bonn' => $positions['Bonn']] + $towns;
}
?>
<div class="sk-areamap" data-map<?php echo $has_osm ? ' data-map-src="' . esc_url($osm_src) . '"' : ''; ?>>
    <svg class="sk-areamap__svg" viewBox="0 0 480 360" role="img" aria-labelledby="sk-areamap-title">
        <title id="sk-areamap-title"><?php echo esc_html($contact['map_label'] !== '' ? $contact['map_label'] : __('Einsatzgebiet', 'site-kit')); ?></title>
        <defs>
            <pattern id="sk-areamap-grid" width="24" height="24" patternUnits="userSpaceOnUse">
                <path d="M24 0H0v24" fill="none" stroke="currentColor" stroke-opacity=".07"/>
            </pattern>
        </defs>
        <rect width="480" height="360" fill="url(#sk-areamap-grid)"/>

        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
            <circle class="sk-areamap__ring" cx="220" cy="190" r="110"/>
            <circle class="sk-areamap__ring" cx="220" cy="190" r="220"/>
            <path class="sk-areamap__river" d="M327 362 309 307 289 267 271 238 243 209 228 183 208 159 189 135 158 99 131 73 55 -2"/>
            <path class="sk-areamap__river sk-areamap__river--small" d="M420 150 360 142 306 123 274 128 197 142"/>
        </g>

        <g class="sk-areamap__scale" font-size="10">
            <text x="226" y="76">10 km</text>
            <text x="446" y="184">20 km</text>
            <text x="250" y="232" text-anchor="middle" transform="rotate(53 250 232)">Rhein</text>
        </g>

        <g class="sk-areamap__towns">
            <?php foreach ($towns as $name => [$x, $y, $side]) : ?>
                <?php $is_hq = $name === 'Bonn'; ?>
                <g class="sk-areamap__town<?php echo $is_hq ? ' is-hq' : ''; ?>">
                    <?php if ($is_hq) : ?>
                        <circle cx="<?php echo (int) $x; ?>" cy="<?php echo (int) $y; ?>" r="16" class="sk-areamap__pulse"/>
                    <?php endif; ?>
                    <circle cx="<?php echo (int) $x; ?>" cy="<?php echo (int) $y; ?>" r="<?php echo $is_hq ? 6 : 3.5; ?>"/>
                    <?php
                    [$tx, $ty, $anchor] = match ($side) {
                        'l'     => [$x - 9, $y + 4, 'end'],
                        't'     => [$x, $y - 9, 'middle'],
                        'b'     => [$x, $y + 17, 'middle'],
                        default => [$x + 9, $y + 4, 'start'],
                    };
                    ?>
                    <text x="<?php echo (int) $tx; ?>" y="<?php echo (int) $ty; ?>" text-anchor="<?php echo esc_attr($anchor); ?>"><?php echo esc_html($name); ?></text>
                </g>
            <?php endforeach; ?>
        </g>
    </svg>

    <div class="sk-areamap__foot">
        <div class="sk-areamap__legend">
            <span class="sk-label"><?php esc_html_e('Einsatzgebiet', 'site-kit'); ?></span>
            <strong><?php esc_html_e('Bonn & Rhein-Sieg-Kreis', 'site-kit'); ?></strong>
            <span><?php esc_html_e('Radius ca. 25 km um Bonn – weitere Orte auf Anfrage', 'site-kit'); ?></span>
        </div>

    <?php if ($has_osm) : ?>
        <div class="sk-areamap__load">
            <button class="sk-btn sk-btn--light sk-btn--sm" type="button" data-map-load>
                <?php Util\icon('location', ''); ?>
                <?php esc_html_e('Interaktive Karte laden', 'site-kit'); ?>
            </button>
            <small><?php esc_html_e('Beim Laden werden Daten (u. a. Ihre IP-Adresse) an OpenStreetMap übertragen.', 'site-kit'); ?></small>
        </div>
    <?php endif; ?>
    </div>
</div>
