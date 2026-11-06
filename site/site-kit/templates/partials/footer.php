<?php
/**
 * Partial: closing call to action, footer, back-to-top button and closing tags.
 *
 * Everything comes from config.php (contact, hours, emergency, navigation)
 * and Content\services(), so the footer never needs editing per project.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$contact   = Config\contact();
$emergency = Config\emergency();
$socials   = Config\socials();
$nav       = Config\nav();
$hours     = Util\hours_rows();
$whatsapp  = Util\whatsapp_url();
$services  = Content\services();
$svc_url   = Config\page_url('services');
$about_url = Config\page_url('about');

$legal = [];
foreach ((array) Config\get('footer_nav', []) as $item) {
    if (!is_array($item) || empty($item['slug'])) {
        continue;
    }
    $url = Config\resolve_location((string) $item['slug']);
    if ($url !== '') {
        $legal[] = ['label' => (string) ($item['label'] ?? $item['slug']), 'url' => $url];
    }
}

$cta     = (array) Config\get('cta', []);
$cta_url = !empty($cta['slug']) ? Config\resolve_location((string) $cta['slug']) : '';
?>
<footer class="sk-footer">
    <?php if ($cta_url !== '') : ?>
        <div class="sk-wrap">
            <div class="sk-footer__cta">
                <div>
                    <p class="sk-label"><?php esc_html_e('Bereit, wenn Sie es sind', 'site-kit'); ?></p>
                    <p class="sk-footer__claim">
                        <?php esc_html_e('Ihr Projekt.', 'site-kit'); ?>
                        <span><?php esc_html_e('Unser Meisterbetrieb.', 'site-kit'); ?></span>
                    </p>
                </div>
                <div class="sk-footer__actions">
                    <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($cta_url); ?>">
                        <?php echo esc_html((string) ($cta['label'] ?? __('Kontakt', 'site-kit'))); ?>
                        <?php Util\icon('arrow-right'); ?>
                    </a>
                    <?php if ($emergency['enabled']) : ?>
                        <a class="sk-btn sk-btn--ghost" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                            <span class="sk-live" aria-hidden="true"></span>
                            <?php echo esc_html($emergency['label'] . ': ' . $emergency['phone']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="sk-wrap">
        <div class="sk-footer__grid">
            <div class="sk-footer__brand">
                <a class="sk-brand sk-brand--light" href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo esc_url(Util\asset('img/site/logo-light.svg')); ?>" alt="" width="44" height="44" loading="lazy" class="sk-brand__mark">
                    <span class="sk-brand__text">
                        <span class="sk-brand__name"><?php echo esc_html(Config\brand_short()); ?></span>
                        <span class="sk-brand__tag"><?php echo esc_html(Config\tagline()); ?></span>
                    </span>
                </a>
                <p><?php echo esc_html((string) Config\get('brand.description', '')); ?></p>

                <?php if ($contact['street'] !== '' || $contact['city'] !== '') : ?>
                    <address class="sk-footer__addr">
                        <?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?><br>
                        <?php echo esc_html($contact['street']); ?><br>
                        <?php echo esc_html($contact['city']); ?>
                    </address>
                <?php endif; ?>

                <?php if ($socials) : ?>
                    <div class="sk-socials">
                        <?php foreach ($socials as $network => $url) : ?>
                            <a href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr(ucfirst($network)); ?>" rel="noopener">
                                <?php Util\icon(\SiteKit\Icons\social_icon($network), ''); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($svc_url !== '') : ?>
                <div class="sk-footer__col">
                    <h2><?php esc_html_e('Leistungen', 'site-kit'); ?></h2>
                    <ul>
                        <?php foreach ($services as $key => $service) : ?>
                            <li><a href="<?php echo esc_url($svc_url . '#' . $key); ?>"><?php echo esc_html($service['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="sk-footer__col">
                <h2><?php esc_html_e('Unternehmen', 'site-kit'); ?></h2>
                <ul>
                    <?php foreach ($nav as $item) : ?>
                        <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                    <?php if ($about_url !== '') : ?>
                        <li><a href="<?php echo esc_url($about_url . '#karriere'); ?>"><?php esc_html_e('Karriere', 'site-kit'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sk-footer__col">
                <h2><?php esc_html_e('Kontakt', 'site-kit'); ?></h2>
                <ul>
                    <?php if ($contact['phone'] !== '') : ?>
                        <li><a href="<?php echo esc_url(Util\tel($contact['phone']), ['tel']); ?>"><?php echo esc_html($contact['phone']); ?></a></li>
                    <?php endif; ?>
                    <?php if ($contact['email'] !== '') : ?>
                        <li><a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a></li>
                    <?php endif; ?>
                    <?php if ($whatsapp !== '') : ?>
                        <li><a href="<?php echo esc_url($whatsapp); ?>" rel="noopener" target="_blank"><?php esc_html_e('WhatsApp', 'site-kit'); ?></a></li>
                    <?php endif; ?>
                </ul>

                <?php if ($hours) : ?>
                    <dl class="sk-hours sk-hours--compact">
                        <?php foreach ($hours as $row) : ?>
                            <div class="sk-hours__row<?php echo $row['closed'] ? ' is-closed' : ''; ?>">
                                <dt><?php echo esc_html($row['days']); ?></dt>
                                <dd><?php echo esc_html($row['time']); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sk-footer__bottom">
        <div class="sk-wrap">
            <span>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?></span>
            <span class="sk-footer__legal">
                <?php foreach ($legal as $item) : ?>
                    <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
            </span>
        </div>
    </div>
</footer>

<button class="sk-totop" type="button" aria-label="<?php esc_attr_e('Nach oben', 'site-kit'); ?>">
    <?php Util\icon('arrow-up', ''); ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
