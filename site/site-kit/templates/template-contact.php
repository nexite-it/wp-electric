<?php
/**
 * Template: contact page.
 *
 * Contact details, optional OpenStreetMap embed (only when a bounding box is
 * configured — no map, no third-party request) and the contact form.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$intro   = Content\page_intro('contact');
$contact = Config\contact();
$socials = Config\socials();
$faq     = Content\faq();

[$ok, $notice] = Util\form_notice([
    'sent'    => __('Thank you — your message reached us and we will reply soon.', 'site-kit'),
    'invalid' => __('Please check your name, e-mail address and message (at least 10 characters).', 'site-kit'),
    'nonce'   => __('The form has expired. Please send it again.', 'site-kit'),
    'bot'     => __('That looked automated. Please use the form manually.', 'site-kit'),
    'rate'    => __('Too many submissions. Please try again later.', 'site-kit'),
    'failed'  => __('The message could not be sent. Please try again later.', 'site-kit'),
    'generic' => __('Something went wrong. Please try again later.', 'site-kit'),
]);

$address = trim(implode(', ', array_filter([$contact['street'], $contact['city']])));
$has_map = $contact['map'] !== '' && preg_match('/^-?[\d.]+,(-?[\d.]+),(-?[\d.]+),(-?[\d.]+)$/', $contact['map']);

\SiteKit\Template\partial('header', ['page_key' => 'contact']);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => get_the_title() ?: $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => $intro['title']]],
    ]);
    ?>

    <section class="sk-section">
        <div class="sk-wrap">
            <div class="sk-contact">
                <div class="reveal">
                    <?php if ($address !== '') : ?>
                        <div class="sk-infobox">
                            <div class="sk-infobox__icon"><?php Util\icon('location', ''); ?></div>
                            <div>
                                <b><?php esc_html_e('Address', 'site-kit'); ?></b>
                                <span>
                                    <?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?><br>
                                    <?php echo esc_html($contact['street']); ?><br>
                                    <?php echo esc_html($contact['city']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact['email'] !== '') : ?>
                        <div class="sk-infobox">
                            <div class="sk-infobox__icon"><?php Util\icon('mail', ''); ?></div>
                            <div>
                                <b><?php esc_html_e('E-mail', 'site-kit'); ?></b>
                                <span><a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact['phone'] !== '') : ?>
                        <div class="sk-infobox">
                            <div class="sk-infobox__icon"><?php Util\icon('phone', ''); ?></div>
                            <div>
                                <b><?php esc_html_e('Phone', 'site-kit'); ?></b>
                                <span><a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $contact['phone'])); ?>"><?php echo esc_html($contact['phone']); ?></a></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($socials) : ?>
                        <div class="sk-infobox">
                            <div class="sk-infobox__icon"><?php Util\icon('network', ''); ?></div>
                            <div>
                                <b><?php esc_html_e('Social', 'site-kit'); ?></b>
                                <span>
                                    <?php foreach ($socials as $network => $url) : ?>
                                        <a href="<?php echo esc_url($url); ?>" rel="noopener"><?php echo esc_html(ucfirst($network)); ?></a><br>
                                    <?php endforeach; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($has_map) : ?>
                        <div class="sk-map">
                            <iframe title="<?php echo esc_attr($contact['map_label'] !== '' ? $contact['map_label'] : __('Map', 'site-kit')); ?>"
                                    src="<?php echo esc_url('https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($contact['map']) . '&amp;layer=mapnik'); ?>"
                                    loading="lazy" referrerpolicy="no-referrer"></iframe>
                        </div>
                        <p class="sk-fineprint"><?php esc_html_e('Map: © OpenStreetMap contributors, ODbL. Loaded only when scrolled into view.', 'site-kit'); ?></p>
                    <?php endif; ?>
                </div>

                <div class="reveal" data-d="1">
                    <?php Util\notice($ok, $notice); ?>
                    <?php \SiteKit\Template\partial('contact-form'); ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($faq) : ?>
        <section class="sk-section sk-section--tint">
            <div class="sk-wrap sk-wrap--narrow">
                <?php
                Util\head_row([
                    'eyebrow' => __('Questions', 'site-kit'),
                    'title'   => __('Frequently asked', 'site-kit'),
                ]);
                ?>
                <div class="sk-faq">
                    <?php foreach ($faq as $i => $item) : ?>
                        <details class="sk-faq__item reveal" data-d="<?php echo esc_attr((string) $i); ?>"<?php echo $i === 0 ? ' open' : ''; ?>>
                            <summary><?php echo esc_html($item['q']); ?></summary>
                            <p><?php echo esc_html($item['a']); ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
\SiteKit\Template\partial('footer');
