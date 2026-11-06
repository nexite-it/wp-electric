<?php
/**
 * Partial: contact form.
 *
 * @var array{anchor?: string, heading?: string, compact?: bool} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$anchor  = (string) ($anchor ?? 'contact-form');
$heading = (string) ($heading ?? __('Send us a message', 'site-kit'));
$topics  = [
    'general'   => __('General question', 'site-kit'),
    'project'   => __('A project or partnership', 'site-kit'),
    'press'     => __('Press & media', 'site-kit'),
    'volunteer' => __('Volunteering', 'site-kit'),
];
?>
<form class="sk-form" id="<?php echo esc_attr($anchor); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="sk_contact">
    <?php Util\form_security_fields('sk_contact'); ?>

    <?php if ($heading !== '') : ?>
        <h2 class="sk-form__title"><?php echo esc_html($heading); ?></h2>
    <?php endif; ?>

    <div class="sk-form__grid">
        <div class="sk-field">
            <label for="sk-name"><?php esc_html_e('Name', 'site-kit'); ?> *</label>
            <input id="sk-name" name="sk_name" type="text" required autocomplete="name">
        </div>
        <div class="sk-field">
            <label for="sk-email"><?php esc_html_e('E-mail', 'site-kit'); ?> *</label>
            <input id="sk-email" name="sk_email" type="email" required autocomplete="email">
        </div>
        <div class="sk-field sk-field--wide">
            <label for="sk-topic"><?php esc_html_e('Topic', 'site-kit'); ?></label>
            <select id="sk-topic" name="sk_topic">
                <?php foreach ($topics as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="sk-field sk-field--wide">
            <label for="sk-subject"><?php esc_html_e('Subject', 'site-kit'); ?></label>
            <input id="sk-subject" name="sk_subject" type="text">
        </div>
        <div class="sk-field sk-field--wide">
            <label for="sk-message"><?php esc_html_e('Message', 'site-kit'); ?> *</label>
            <textarea id="sk-message" name="sk_message" rows="6" required minlength="10"></textarea>
        </div>
    </div>

    <div class="sk-form__foot">
        <button class="sk-btn sk-btn--primary" type="submit">
            <?php esc_html_e('Send message', 'site-kit'); ?>
            <?php Util\icon('arrow-right', ''); ?>
        </button>
        <span class="sk-form__note">
            <?php esc_html_e('Your details are used only to answer your message and are not stored on this website.', 'site-kit'); ?>
        </span>
    </div>
</form>
