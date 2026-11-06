<?php
/**
 * Partial: document head, header and mobile navigation.
 *
 * Expects (optional):
 *   $page_key  Slug of the current nav item, used for the active state.
 *
 * Everything user-facing comes from config.php, so header and footer never
 * need editing for a new project.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$page_key    = isset($page_key) ? (string) $page_key : '';
$nav_items   = Config\nav();
$cta         = (array) Config\get('cta', []);
$cta_url     = !empty($cta['slug']) ? Config\resolve_location((string) $cta['slug']) : '';
$socials     = Config\socials();
$home        = home_url('/');
$logo        = Util\asset('img/site/logo.svg');
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

<a class="sk-skip" href="#content"><?php esc_html_e('Skip to content', 'site-kit'); ?></a>

<header class="sk-header" data-header>
    <div class="sk-wrap sk-header__inner">
        <a class="sk-brand" href="<?php echo esc_url($home); ?>">
            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(Config\brand_name()); ?>" width="44" height="44" class="sk-brand__mark">
            <span class="sk-brand__text">
                <span class="sk-brand__name"><?php echo esc_html(Config\brand_short()); ?></span>
                <span class="sk-brand__tag"><?php echo esc_html(Config\tagline()); ?></span>
            </span>
        </a>

        <?php if ($nav_items) : ?>
        <nav class="sk-nav" aria-label="<?php esc_attr_e('Primary navigation', 'site-kit'); ?>">
            <ul class="sk-nav__list">
                <?php foreach ($nav_items as $item) : ?>
                    <li>
                        <a class="sk-nav__link<?php echo $item['slug'] === $page_key ? ' is-active' : ''; ?>"
                           href="<?php echo esc_url($item['url']); ?>"
                           <?php echo $item['slug'] === $page_key ? ' aria-current="page"' : ''; ?>>
                            <?php echo esc_html($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <div class="sk-header__actions">
            <?php if ($cta_url !== '') : ?>
                <a class="sk-btn sk-btn--accent sk-btn--sm" href="<?php echo esc_url($cta_url); ?>">
                    <?php echo esc_html((string) ($cta['label'] ?? __('Contact', 'site-kit'))); ?>
                </a>
            <?php endif; ?>

            <button class="sk-burger" type="button" aria-label="<?php esc_attr_e('Open menu', 'site-kit'); ?>" aria-expanded="false" aria-controls="sk-mobile-nav">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="sk-mnav" id="sk-mobile-nav" aria-hidden="true">
    <div class="sk-mnav__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Menu', 'site-kit'); ?>">
        <button class="sk-mnav__close" type="button" aria-label="<?php esc_attr_e('Close menu', 'site-kit'); ?>">
            <?php Util\icon('close', ''); ?>
        </button>

        <?php foreach ($nav_items as $item) : ?>
            <a class="sk-mnav__link" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
        <?php endforeach; ?>

        <?php if ($cta_url !== '') : ?>
            <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html((string) ($cta['label'] ?? '')); ?></a>
        <?php endif; ?>

        <?php if ($socials) : ?>
            <div class="sk-mnav__socials">
                <?php foreach ($socials as $network => $url) : ?>
                    <a href="<?php echo esc_url($url); ?>" rel="noopener" aria-label="<?php echo esc_attr(ucfirst($network)); ?>">
                        <?php Util\icon(\SiteKit\Icons\social_icon($network), ''); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
