<?php
/**
 * Template: 404.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$links = array_slice(Config\nav(), 0, 4);

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <section class="sk-pagehero sk-pagehero--tall">
        <div class="sk-wrap">
            <p class="sk-eyebrow"><?php esc_html_e('Error 404', 'site-kit'); ?></p>
            <h1><?php esc_html_e('This page does not exist.', 'site-kit'); ?></h1>
            <p class="sk-lead"><?php esc_html_e('The link may be old, or there might be a typo. These pages will get you further:', 'site-kit'); ?></p>

            <div class="sk-actions">
                <a class="sk-btn sk-btn--accent" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php esc_html_e('Back to the start page', 'site-kit'); ?>
                </a>
                <?php foreach ($links as $link) : ?>
                    <a class="sk-btn sk-btn--outline" href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="sk-404-search">
                <?php \SiteKit\Template\partial('search-form'); ?>
            </div>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
