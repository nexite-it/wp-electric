<?php
/**
 * Template: Kontakt.
 *
 * Action hub for lead generation. Left: the project inquiry form (topic
 * preselected via ?leistung=). Right: emergency line, direct contact
 * channels (phone, WhatsApp, e-mail, address), office hours with a live
 * open/closed state and the service-area map (OpenStreetMap on click only).
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

if (have_posts()) {
    the_post();
}

$intro     = Content\page_intro('contact');
$contact   = Config\contact();
$emergency = Config\emergency();
$whatsapp  = Util\whatsapp_url();
$hours     = Util\hours_rows();
$summary   = Util\hours_summary();

[$ok, $notice] = Util\form_notice([
    'sent'    => __('Vielen Dank! Ihre Anfrage ist bei uns angekommen – wir melden uns werktags innerhalb von 24 Stunden.', 'site-kit'),
    'invalid' => __('Bitte prüfen Sie Name, E-Mail-Adresse und Nachricht (mindestens 10 Zeichen).', 'site-kit'),
    'consent' => __('Bitte bestätigen Sie die Einwilligung zur Verarbeitung Ihrer Angaben.', 'site-kit'),
    'phone'   => __('Für einen Rückruf benötigen wir Ihre Telefonnummer.', 'site-kit'),
    'nonce'   => __('Das Formular ist abgelaufen. Bitte senden Sie es erneut.', 'site-kit'),
    'bot'     => __('Das ging etwas zu schnell. Bitte senden Sie das Formular erneut.', 'site-kit'),
    'rate'    => __('Zu viele Anfragen in kurzer Zeit. Bitte versuchen Sie es später erneut oder rufen Sie uns an.', 'site-kit'),
    'failed'  => __('Die Nachricht konnte nicht gesendet werden. Bitte rufen Sie uns an oder schreiben Sie eine E-Mail.', 'site-kit'),
    'generic' => __('Etwas ist schiefgelaufen. Bitte versuchen Sie es später erneut.', 'site-kit'),
]);

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => get_the_title() ?: $intro['eyebrow']]],
    ]);
    ?>

    <section class="sk-section sk-hub">
        <div class="sk-wrap sk-hub__grid">
            <div class="sk-hub__form">
                <?php Util\notice($ok, $notice); ?>
                <?php \SiteKit\Template\partial('contact-form'); ?>
            </div>

            <aside class="sk-hub__aside" aria-label="<?php esc_attr_e('Direkter Kontakt', 'site-kit'); ?>">
                <?php if ($emergency['enabled']) : ?>
                    <a class="sk-sos" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                        <span class="sk-sos__label"><span class="sk-live" aria-hidden="true"></span><?php echo esc_html($emergency['label']); ?></span>
                        <span class="sk-sos__num"><?php Util\icon('phone', ''); ?><?php echo esc_html($emergency['phone']); ?></span>
                        <?php if ($emergency['note'] !== '') : ?>
                            <span class="sk-sos__note"><?php echo esc_html($emergency['note']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <div class="sk-panel">
                    <p class="sk-label"><?php esc_html_e('Direkter Draht', 'site-kit'); ?></p>
                    <ul class="sk-direct">
                        <?php if ($contact['phone'] !== '') : ?>
                            <li>
                                <a href="<?php echo esc_url(Util\tel($contact['phone']), ['tel']); ?>">
                                    <span class="sk-direct__icon"><?php Util\icon('phone', ''); ?></span>
                                    <span><small><?php esc_html_e('Büro', 'site-kit'); ?></small><b><?php echo esc_html($contact['phone']); ?></b></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($whatsapp !== '') : ?>
                            <li>
                                <a class="sk-direct__wa" href="<?php echo esc_url($whatsapp); ?>" rel="noopener" target="_blank">
                                    <span class="sk-direct__icon"><?php Util\icon('whatsapp', ''); ?></span>
                                    <span><small><?php esc_html_e('WhatsApp', 'site-kit'); ?></small><b><?php esc_html_e('Fotos & Fragen schicken', 'site-kit'); ?></b></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contact['email'] !== '') : ?>
                            <li>
                                <a href="mailto:<?php echo esc_attr($contact['email']); ?>">
                                    <span class="sk-direct__icon"><?php Util\icon('mail', ''); ?></span>
                                    <span><small><?php esc_html_e('E-Mail', 'site-kit'); ?></small><b><?php echo esc_html($contact['email']); ?></b></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contact['street'] !== '') : ?>
                            <li>
                                <span class="sk-direct__static">
                                    <span class="sk-direct__icon"><?php Util\icon('location', ''); ?></span>
                                    <span><small><?php esc_html_e('Betrieb', 'site-kit'); ?></small><b><?php echo esc_html($contact['street'] . ', ' . $contact['city']); ?></b></span>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ($hours) : ?>
                    <div class="sk-panel">
                        <div class="sk-panel__head">
                            <p class="sk-label"><?php esc_html_e('Bürozeiten', 'site-kit'); ?></p>
                            <span class="sk-status" data-open-status>
                                <span class="sk-status__dot" aria-hidden="true"></span>
                                <span data-open-text><?php echo esc_html($summary); ?></span>
                            </span>
                        </div>
                        <dl class="sk-hours">
                            <?php foreach ($hours as $row) : ?>
                                <div class="sk-hours__row<?php echo $row['closed'] ? ' is-closed' : ''; ?>">
                                    <dt><?php echo esc_html($row['days']); ?></dt>
                                    <dd><?php echo esc_html($row['time']); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                        <?php if ($emergency['enabled']) : ?>
                            <p class="sk-panel__note"><?php echo esc_html(sprintf(__('Außerhalb der Bürozeiten erreichen Sie unseren %s.', 'site-kit'), $emergency['label'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php \SiteKit\Template\partial('area-map'); ?>
            </aside>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
