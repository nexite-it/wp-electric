<?php
/**
 * Partial: document head, utility bar, sticky header, mobile navigation and
 * the mobile call bar.
 *
 * Expects (optional):
 *   $page_key  Slug of the current nav item. Detected automatically when
 *              omitted, so templates rarely need to pass it.
 *
 * Conversion rules this header follows:
 *   - the emergency number is visible on every page, at every scroll depth
 *     (sticky header on desktop, fixed call bar on phones)
 *   - "Projekt anfragen" is the only accent-coloured button in the header
 *   - the header never auto-hides; it is the site's phone book
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$nav_items = Config\nav();
$cta       = (array) Config\get('cta', []);
$cta_url   = !empty($cta['slug']) ? Config\resolve_location((string) $cta['slug']) : '';
$contact   = Config\contact();
$emergency = Config\emergency();
$whatsapp  = Util\whatsapp_url();
$hours     = Util\hours_summary();
$home      = home_url('/');
$logo      = Util\asset('img/site/logo.svg');

if (!isset($page_key) || (string) $page_key === '') {
    $object   = get_queried_object();
    $page_key = is_front_page() ? 'home' : ($object instanceof \WP_Post && is_page() ? $object->post_name : '');
}
$page_key = (string) $page_key;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sk-skip" href="#content"><?php esc_html_e('Zum Inhalt springen', 'site-kit'); ?></a>

<?php if ($hours !== '') : ?>
    <script type="application/json" id="sk-hours"><?php echo Util\hours_json(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode of validated config ?></script>
<?php endif; ?>

<div class="sk-topbar">
    <div class="sk-wrap sk-topbar__inner">
        <p class="sk-topbar__item">
            <?php Util\icon('location', ''); ?>
            <span><?php echo esc_html(Config\tagline()); ?> · <?php esc_html_e('Bonn & Rhein-Sieg-Kreis', 'site-kit'); ?></span>
        </p>

        <?php if ($hours !== '') : ?>
            <p class="sk-topbar__item sk-status" data-open-status>
                <span class="sk-status__dot" aria-hidden="true"></span>
                <span data-open-text><?php echo esc_html(sprintf(__('Büro: %s', 'site-kit'), $hours)); ?></span>
            </p>
        <?php endif; ?>

        <div class="sk-topbar__links">
            <?php if ($contact['phone'] !== '') : ?>
                <a href="<?php echo esc_url(Util\tel($contact['phone']), ['tel']); ?>">
                    <?php Util\icon('phone', ''); ?>
                    <span><?php echo esc_html(sprintf(__('Büro %s', 'site-kit'), $contact['phone'])); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($whatsapp !== '') : ?>
                <a href="<?php echo esc_url($whatsapp); ?>" rel="noopener" target="_blank">
                    <?php Util\icon('whatsapp', ''); ?>
                    <span><?php esc_html_e('WhatsApp', 'site-kit'); ?></span>
                </a>
            <?php endif; ?>
            <?php if ($contact['email'] !== '') : ?>
                <a href="mailto:<?php echo esc_attr($contact['email']); ?>">
                    <?php Util\icon('mail', ''); ?>
                    <span><?php echo esc_html($contact['email']); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="sk-header" data-header>
    <div class="sk-wrap sk-header__inner">
        <a class="sk-brand" href="<?php echo esc_url($home); ?>"<?php echo $page_key === 'home' ? ' aria-current="page"' : ''; ?>>
            <img src="<?php echo esc_url($logo); ?>" alt="" width="44" height="44" class="sk-brand__mark">
            <span class="sk-brand__text">
                <span class="sk-brand__name"><?php echo esc_html(Config\brand_short()); ?></span>
                <span class="sk-brand__tag"><?php echo esc_html(Config\tagline()); ?></span>
            </span>
        </a>

        <?php if ($nav_items) : ?>
        <nav class="sk-nav" aria-label="<?php esc_attr_e('Hauptnavigation', 'site-kit'); ?>">
            <ul class="sk-nav__list">
                <?php foreach ($nav_items as $item) : ?>
                    <?php $active = $item['slug'] === $page_key; ?>
                    <li>
                        <a class="sk-nav__link<?php echo $active ? ' is-active' : ''; ?>"
                           href="<?php echo esc_url($item['url']); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="sk-header__actions">
            <?php if ($emergency['enabled']) : ?>
                <a class="sk-hotline" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                    <span class="sk-hotline__icon" aria-hidden="true"><?php Util\icon('phone', ''); ?></span>
                    <span class="sk-hotline__text">
                        <span class="sk-hotline__label"><span class="sk-live" aria-hidden="true"></span><?php echo esc_html($emergency['label']); ?></span>
                        <span class="sk-hotline__num"><?php echo esc_html($emergency['phone']); ?></span>
                    </span>
                </a>
            <?php endif; ?>

            <?php if ($cta_url !== '') : ?>
                <a class="sk-btn sk-btn--accent sk-header__cta" href="<?php echo esc_url($cta_url); ?>">
                    <?php echo esc_html((string) ($cta['label'] ?? __('Kontakt', 'site-kit'))); ?>
                    <?php Util\icon('arrow-right'); ?>
                </a>
            <?php endif; ?>

            <button class="sk-burger" type="button" aria-label="<?php esc_attr_e('Menü öffnen', 'site-kit'); ?>" aria-expanded="false" aria-controls="sk-mobile-nav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="sk-mnav" id="sk-mobile-nav" aria-hidden="true">
    <div class="sk-mnav__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Menü', 'site-kit'); ?>">
        <button class="sk-mnav__close" type="button" aria-label="<?php esc_attr_e('Menü schließen', 'site-kit'); ?>">
            <?php Util\icon('close', ''); ?>
        </button>

        <?php foreach ($nav_items as $item) : ?>
            <a class="sk-mnav__link<?php echo $item['slug'] === $page_key ? ' is-active' : ''; ?>" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
        <?php endforeach; ?>

        <?php if ($cta_url !== '') : ?>
            <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html((string) ($cta['label'] ?? '')); ?></a>
        <?php endif; ?>

        <?php if ($emergency['enabled']) : ?>
            <a class="sk-mnav__sos" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                <small><span class="sk-live" aria-hidden="true"></span><?php echo esc_html($emergency['label']); ?></small>
                <strong><?php echo esc_html($emergency['phone']); ?></strong>
            </a>
        <?php endif; ?>

        <?php if ($hours !== '') : ?>
            <p class="sk-mnav__meta"><?php echo esc_html(sprintf(__('Büro: %s', 'site-kit'), $hours)); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php if ($emergency['enabled'] || $cta_url !== '') : ?>
    <nav class="sk-callbar" aria-label="<?php esc_attr_e('Schnellkontakt', 'site-kit'); ?>">
        <?php if ($emergency['enabled']) : ?>
            <a class="sk-callbar__sos" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                <?php Util\icon('phone', ''); ?>
                <span><?php esc_html_e('Notdienst', 'site-kit'); ?></span>
            </a>
        <?php endif; ?>
        <?php if ($whatsapp !== '') : ?>
            <a href="<?php echo esc_url($whatsapp); ?>" rel="noopener" target="_blank">
                <?php Util\icon('whatsapp', ''); ?>
                <span><?php esc_html_e('WhatsApp', 'site-kit'); ?></span>
            </a>
        <?php endif; ?>
        <?php if ($cta_url !== '') : ?>
            <a class="sk-callbar__cta" href="<?php echo esc_url($cta_url); ?>">
                <?php Util\icon('mail', ''); ?>
                <span><?php esc_html_e('Anfrage', 'site-kit'); ?></span>
            </a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
