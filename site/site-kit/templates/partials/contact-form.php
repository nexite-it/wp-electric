<?php
/**
 * Partial: project inquiry form.
 *
 * The topic is preselected from ?leistung=<key> (service pages, recruiting
 * banner link here with it), and the message placeholder follows the chosen
 * topic so visitors know which details help us quote quickly.
 *
 * Submissions are handled by SiteKit\Forms\handle_contact().
 *
 * @var array{anchor?: string, heading?: string, intro?: string} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$anchor  = (string) ($anchor ?? 'anfrage');
$heading = (string) ($heading ?? __('Projekt anfragen', 'site-kit'));
$intro   = (string) ($intro ?? __('Ein paar Eckdaten genügen – den Rest klären wir im Gespräch oder beim Vor-Ort-Termin.', 'site-kit'));
$topics  = Content\form_topics();
$privacy = Config\page_url('privacy');

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preselection
$chosen = isset($_GET['leistung']) ? sanitize_key(wp_unslash((string) $_GET['leistung'])) : '';
if (!isset($topics[$chosen])) {
    $chosen = (string) array_key_first($topics);
}
?>
<form class="sk-form" id="<?php echo esc_attr($anchor); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-sending="<?php esc_attr_e('Wird gesendet …', 'site-kit'); ?>">
    <input type="hidden" name="action" value="sk_contact">
    <?php Util\form_security_fields('sk_contact'); ?>

    <div class="sk-form__head">
        <?php if ($heading !== '') : ?>
            <h2 class="sk-form__title"><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>
        <?php if ($intro !== '') : ?>
            <p class="sk-form__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>
    </div>

    <fieldset class="sk-topics">
        <legend><?php esc_html_e('Worum geht es?', 'site-kit'); ?></legend>
        <div class="sk-topics__grid">
            <?php foreach ($topics as $key => $topic) : ?>
                <label class="sk-topic">
                    <input type="radio" name="sk_topic" value="<?php echo esc_attr($key); ?>"
                           data-hint="<?php echo esc_attr($topic['hint']); ?>"<?php checked($key, $chosen); ?>>
                    <span class="sk-topic__box">
                        <?php Util\icon($topic['icon'], ''); ?>
                        <span><?php echo esc_html($topic['label']); ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="sk-form__grid">
        <div class="sk-field">
            <label for="sk-name"><?php esc_html_e('Name', 'site-kit'); ?> <span class="sk-req" aria-hidden="true">*</span></label>
            <input id="sk-name" name="sk_name" type="text" required autocomplete="name">
        </div>
        <div class="sk-field">
            <label for="sk-phone"><?php esc_html_e('Telefon', 'site-kit'); ?></label>
            <input id="sk-phone" name="sk_phone" type="tel" autocomplete="tel" inputmode="tel">
        </div>
        <div class="sk-field">
            <label for="sk-email"><?php esc_html_e('E-Mail', 'site-kit'); ?> <span class="sk-req" aria-hidden="true">*</span></label>
            <input id="sk-email" name="sk_email" type="email" required autocomplete="email">
        </div>
        <div class="sk-field">
            <label for="sk-location"><?php esc_html_e('PLZ / Ort des Projekts', 'site-kit'); ?></label>
            <input id="sk-location" name="sk_location" type="text" autocomplete="postal-code" placeholder="<?php esc_attr_e('z. B. 53113 Bonn', 'site-kit'); ?>">
        </div>
        <div class="sk-field sk-field--wide">
            <label for="sk-message"><?php esc_html_e('Ihre Nachricht', 'site-kit'); ?> <span class="sk-req" aria-hidden="true">*</span></label>
            <textarea id="sk-message" name="sk_message" rows="6" required minlength="10"
                      placeholder="<?php echo esc_attr($topics[$chosen]['hint']); ?>"></textarea>
        </div>
    </div>

    <div class="sk-checks">
        <label class="sk-check">
            <input type="checkbox" name="sk_callback" value="1">
            <span><?php esc_html_e('Bitte rufen Sie mich zurück (Telefonnummer angeben).', 'site-kit'); ?></span>
        </label>
        <label class="sk-check">
            <input type="checkbox" name="sk_consent" value="1" required>
            <span>
                <?php
                if ($privacy !== '') {
                    printf(
                        /* translators: %s: link to the privacy policy */
                        esc_html__('Ich bin einverstanden, dass meine Angaben zur Bearbeitung meiner Anfrage verwendet werden. Details in der %s.', 'site-kit'),
                        '<a href="' . esc_url($privacy) . '">' . esc_html__('Datenschutzerklärung', 'site-kit') . '</a>'
                    );
                } else {
                    esc_html_e('Ich bin einverstanden, dass meine Angaben zur Bearbeitung meiner Anfrage verwendet werden.', 'site-kit');
                }
                ?>
                <span class="sk-req" aria-hidden="true">*</span>
            </span>
        </label>
    </div>

    <div class="sk-form__foot">
        <button class="sk-btn sk-btn--accent sk-btn--lg" type="submit">
            <?php esc_html_e('Anfrage senden', 'site-kit'); ?>
            <?php Util\icon('arrow-right', ''); ?>
        </button>
        <span class="sk-form__note">
            <?php Util\icon('clock', ''); ?>
            <?php esc_html_e('Antwort werktags innerhalb von 24 Stunden. Pflichtfelder sind mit * markiert.', 'site-kit'); ?>
        </span>
    </div>
</form>
