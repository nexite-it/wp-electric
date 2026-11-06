<?php
/**
 * Template: 404.
 *
 * A dead link should still lead to help: the main pages and, prominently,
 * the emergency line.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$links     = array_slice(Config\nav(), 1, 3);
$emergency = Config\emergency();

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <section class="sk-pagehero sk-pagehero--tall">
        <div class="sk-wrap">
            <p class="sk-eyebrow"><?php esc_html_e('Fehler 404', 'site-kit'); ?></p>
            <h1><?php esc_html_e('Diese Seite gibt es nicht.', 'site-kit'); ?></h1>
            <p class="sk-lead"><?php esc_html_e('Vielleicht ist der Link veraltet oder enthält einen Tippfehler. Hier geht es weiter:', 'site-kit'); ?></p>

            <div class="sk-actions">
                <a class="sk-btn sk-btn--accent" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php esc_html_e('Zur Startseite', 'site-kit'); ?>
                    <?php Util\icon('arrow-right'); ?>
                </a>
                <?php foreach ($links as $link) : ?>
                    <a class="sk-btn sk-btn--ghost" href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a>
                <?php endforeach; ?>
            </div>

            <?php if ($emergency['enabled']) : ?>
                <p class="sk-pagehero__meta">
                    <?php esc_html_e('Notfall?', 'site-kit'); ?>
                    <a href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>"><?php echo esc_html($emergency['label'] . ': ' . $emergency['phone']); ?></a>
                </p>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
