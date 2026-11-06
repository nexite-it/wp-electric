<?php
/**
 * Partial: status notice for form results.
 *
 * @var array{ok?: bool, text?: string} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$ok   = (bool) ($ok ?? false);
$text = (string) ($text ?? '');

if ($text === '') {
    return;
}
?>
<div class="sk-notice sk-notice--<?php echo $ok ? 'ok' : 'error'; ?>" role="status">
    <?php Util\icon($ok ? 'check' : 'close', ''); ?>
    <span><?php echo esc_html($text); ?></span>
</div>
