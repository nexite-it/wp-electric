<?php
/**
 * Form handling: contact form and event RSVP.
 *
 * Everything is processed through admin-post.php and answered with a redirect,
 * so the site needs no sessions and no cookies.
 *
 * Spam protection is layered and deliberately boring:
 *   1. nonce          — form must originate from this site
 *   2. honeypot       — hidden field must stay empty
 *   3. time window    — submissions faster than 2 s are rejected
 *   4. rate limit     — per-IP transient on submissions
 *
 * Submissions are e-mailed to config.contact.recipient and are NOT stored in
 * the database (no personal data retention beyond the mailbox).
 */

namespace SiteKit\Forms;

defined('ABSPATH') || exit;

use SiteKit\Config;

/** Register endpoints. */
function init(): void
{
    foreach (['sk_contact', 'sk_rsvp'] as $action) {
        add_action('admin_post_' . $action, __NAMESPACE__ . '\\handle_' . substr($action, 3));
        add_action('admin_post_nopriv_' . $action, __NAMESPACE__ . '\\handle_' . substr($action, 3));
    }
}

/**
 * Redirect back to the referring page with a status in the query string.
 *
 * @param string $anchor Anchor id to append.
 * @param bool   $ok     Success or failure.
 * @param string $code   Machine-readable message code.
 */
function redirect_back(string $anchor, bool $ok, string $code): void
{
    $referer = wp_get_referer();
    $url     = $referer !== false && $referer !== '' ? $referer : home_url('/');

    $url = remove_query_arg(['sk_form', 'sk_msg'], $url);
    $url = add_query_arg(['sk_form' => $ok ? 'ok' : 'err', 'sk_msg' => $code], $url);

    if ($anchor !== '') {
        $url .= '#' . rawurlencode($anchor);
    }

    wp_safe_redirect($url);
    exit;
}

/**
 * Validate nonce, honeypot, timing and rate limit.
 *
 * @param string      $action Nonce action.
 * @param string|null $error  Set to an error code when validation fails.
 */
function guard(string $action, ?string &$error): bool
{
    $error = null;

    $nonce = isset($_POST['sk_nonce']) ? sanitize_key(wp_unslash((string) $_POST['sk_nonce'])) : '';
    if ($nonce === '' || !wp_verify_nonce($nonce, $action)) {
        $error = 'nonce';
        return false;
    }

    if (!empty($_POST['sk_hp'])) {
        $error = 'bot';
        return false;
    }

    $started = isset($_POST['sk_t']) && is_numeric($_POST['sk_t']) ? (int) $_POST['sk_t'] : 0;
    if ($started > 0 && (time() - $started) < 2) {
        $error = 'bot';
        return false;
    }

    if (is_rate_limited()) {
        $error = 'rate';
        return false;
    }

    return true;
}

/** Simple per-IP throttle: 8 submissions per 10 minutes. */
function is_rate_limited(): bool
{
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    $key = 'sk_form_' . md5($ip);
    $hit = (int) get_transient($key);

    if ($hit >= 8) {
        return true;
    }

    set_transient($key, $hit + 1, 10 * MINUTE_IN_SECONDS);

    return false;
}

/** A trimmed, sanitized string field from POST. */
function field(string $key, int $max = 200): string
{
    $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';

    return is_scalar($value) ? mb_substr(sanitize_text_field((string) $value), 0, $max) : '';
}

/** A sanitized multi-line field from POST. */
function textarea(string $key, int $max = 4000): string
{
    $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';

    return is_scalar($value) ? mb_substr(sanitize_textarea_field((string) $value), 0, $max) : '';
}

/** Sanitized e-mail field from POST. */
function email(string $key = 'sk_email'): string
{
    $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';

    return is_scalar($value) ? sanitize_email((string) $value) : '';
}

/**
 * Send a plain-text mail to the configured recipient.
 *
 * @param array<string, string> $fields  Label => value, rendered as a table.
 * @param string                $subject Subject line.
 * @param string                $reply   Reply-To address (visitor's e-mail).
 */
function send(array $fields, string $subject, string $reply = ''): bool
{
    $to  = Config\contact()['recipient'];
    $sit = wp_parse_url(home_url('/'), PHP_URL_HOST) ?: 'website';

    $lines = [];
    foreach ($fields as $label => $value) {
        if ($value === '') {
            continue;
        }
        $lines[] = str_pad($label, 24) . ': ' . $value;
    }

    $body = sprintf(
        /* translators: 1: site URL, 2: form fields, 3: date */
        __("Neue Nachricht über %1\$s\n\n%2\$s\n\n(Gesendet am %3\$s)", 'site-kit'),
        home_url('/'),
        implode("\n", $lines),
        wp_date('d.m.Y, H:i')
    );

    $headers = [];
    if ($reply !== '' && is_email($reply)) {
        $headers[] = 'Reply-To: ' . $reply;
    }

    return wp_mail($to, '[' . $sit . '] ' . $subject, $body, $headers);
}

/**
 * Project inquiry handler (contact page and [sk_contact_form] shortcode).
 *
 * Required: name, a valid e-mail, a message of at least 10 characters and the
 * privacy consent. The topic must be one of Content\form_topics(); anything
 * else is filed as "Sonstiges".
 */
function handle_contact(): void
{
    $anchor = 'anfrage';
    $error  = null;
    if (!guard('sk_contact', $error)) {
        redirect_back($anchor, false, $error ?? 'generic');
    }

    $topics   = \SiteKit\Content\form_topics();
    $topic    = sanitize_key(field('sk_topic', 40));
    $topic    = isset($topics[$topic]) ? $topic : 'sonstiges';
    $name     = field('sk_name');
    $email    = email();
    $phone    = field('sk_phone', 40);
    $location = field('sk_location', 120);
    $message  = textarea('sk_message');
    $callback = !empty($_POST['sk_callback']); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard()
    $consent  = !empty($_POST['sk_consent']); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard()

    if (!$consent) {
        redirect_back($anchor, false, 'consent');
    }
    if ($name === '' || !is_email($email) || mb_strlen($message) < 10) {
        redirect_back($anchor, false, 'invalid');
    }
    if ($callback && $phone === '') {
        redirect_back($anchor, false, 'phone');
    }

    $label = $topics[$topic]['label'];

    $ok = send(
        [
            __('Anliegen', 'site-kit')        => $label,
            __('Name', 'site-kit')            => $name,
            __('E-Mail', 'site-kit')          => $email,
            __('Telefon', 'site-kit')         => $phone,
            __('Rückruf erwünscht', 'site-kit') => $callback ? __('ja', 'site-kit') : __('nein', 'site-kit'),
            __('PLZ / Ort', 'site-kit')       => $location,
            __('Nachricht', 'site-kit')       => "\n" . $message,
        ],
        sprintf(
            /* translators: 1: topic, 2: sender name */
            __('Anfrage %1$s – %2$s', 'site-kit'),
            $label,
            $name
        ),
        $email
    );

    redirect_back($anchor, $ok, $ok ? 'sent' : 'failed');
}

/** Event RSVP handler. */
function handle_rsvp(): void
{
    if (!Config\feature('rsvp', true)) {
        redirect_back('rsvp', false, 'disabled');
    }

    $error = null;
    if (!guard('sk_rsvp', $error)) {
        redirect_back('rsvp', false, $error ?? 'generic');
    }

    $event_id = isset($_POST['sk_event_id']) ? (int) $_POST['sk_event_id'] : 0;
    $name     = field('sk_name');
    $email    = email();
    $guests   = isset($_POST['sk_guests']) ? max(1, min(10, (int) $_POST['sk_guests'])) : 1;

    $event = $event_id ? get_post($event_id) : null;
    if (!$event instanceof \WP_Post || $event->post_type !== 'event' || $event->post_status !== 'publish') {
        redirect_back('rsvp', false, 'invalid');
    }
    if ($name === '' || !is_email($email)) {
        redirect_back('rsvp', false, 'invalid');
    }

    $ok = send(
        [
            __('Event', 'site-kit')  => get_the_title($event),
            __('When', 'site-kit')   => trim(\SiteKit\Util\meta('sk_event_date', $event) . ' ' . \SiteKit\Util\meta('sk_event_time', $event)),
            __('Name', 'site-kit')   => $name,
            __('E-mail', 'site-kit') => $email,
            __('Guests', 'site-kit') => (string) $guests,
        ],
        sprintf(__('Registration: %s', 'site-kit'), get_the_title($event)),
        $email
    );

    redirect_back('rsvp', $ok, $ok ? 'registered' : 'failed');
}
