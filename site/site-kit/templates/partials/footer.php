<?php
/**
 * Partial: footer, back-to-top button and closing tags.
 *
 * All links and labels come from config.php. Footer columns are built from the
 * configured navigation plus the legal pages and social profiles, so they stay
 * in sync with the header without a second list to maintain.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

$contact = Config\contact();
$socials = Config\socials();
$nav     = Config\nav();
$footer  = [];
foreach ((array) Config\get('footer_nav', []) as $item) {
    if (!is_array($item) || empty($item['slug'])) {
        continue;
    }
    $url = Config\resolve_location((string) $item['slug']);
    if ($url !== '') {
        $footer[] = ['label' => (string) ($item['label'] ?? $item['slug']), 'url' => $url];
    }
}

$half   = (int) ceil(count($nav) / 2);
$left   = array_slice($nav, 0, $half);
$right  = array_slice($nav, $half);
$cta    = (array) Config\get('cta', []);
$cta_url = !empty($cta['slug']) ? Config\resolve_location((string) $cta['slug']) : '';
$address = trim(implode(' · ', array_filter([$contact['street'], $contact['city']])));
?>
<footer class="sk-footer">
    <div class="sk-wrap">

        <?php if ($cta_url !== '') : ?>
            <div class="sk-footer__cta">
                <p class="sk-footer__claim">
                    <?php echo esc_html(Config\brand_name()); ?><br>
                    <span class="sk-box sk-box--accent"><?php echo esc_html(Config\tagline()); ?></span>
                </p>
                <div class="sk-footer__actions">
                    <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($cta_url); ?>">
                        <?php echo esc_html((string) ($cta['label'] ?? __('Get in touch', 'site-kit'))); ?>
                        <?php Util\icon('arrow-right'); ?>
                    </a>
                    <?php if ($contact['email'] !== '') : ?>
                        <a class="sk-btn sk-btn--outline" href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="sk-footer__grid">
            <div class="sk-footer__brand">
                <img src="<?php echo esc_url(Util\asset('img/site/logo-light.svg')); ?>"
                     alt="<?php echo esc_attr(Config\brand_name()); ?>" width="44" height="44" loading="lazy">
                <p><?php echo esc_html((string) Config\get('brand.description', '')); ?></p>

                <?php if ($address !== '') : ?>
                    <address class="sk-footer__addr">
                        <?php echo esc_html(Config\brand_name()); ?><?php echo $address !== '' ? ' · ' . esc_html($address) : ''; ?>
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

            <div class="sk-footer__col">
                <h2><?php esc_html_e('Explore', 'site-kit'); ?></h2>
                <ul>
                    <?php foreach ($left as $item) : ?>
                        <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sk-footer__col">
                <h2><?php esc_html_e('More', 'site-kit'); ?></h2>
                <ul>
                    <?php foreach ($right as $item) : ?>
                        <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sk-footer__col">
                <h2><?php esc_html_e('Contact', 'site-kit'); ?></h2>
                <ul>
                    <?php if ($contact['email'] !== '') : ?>
                        <li><a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a></li>
                    <?php endif; ?>
                    <?php if ($contact['phone'] !== '') : ?>
                        <li><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $contact['phone'])); ?>"><?php echo esc_html($contact['phone']); ?></a></li>
                    <?php endif; ?>
                    <?php foreach ($footer as $item) : ?>
                        <li><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="sk-footer__bottom">
        <div class="sk-wrap">
            <span>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?></span>
            <span class="sk-footer__legal">
                <?php foreach ($footer as $item) : ?>
                    <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
            </span>
        </div>
    </div>
</footer>

<button class="sk-totop" type="button" aria-label="<?php esc_attr_e('Back to top', 'site-kit'); ?>">
    <?php Util\icon('arrow-up', ''); ?>
</button>

<?php wp_footer(); ?>
</body>
</html>
