<?php
/**
 * Partial: section heading row.
 *
 * @var array{eyebrow?: string, title?: string, side?: string, action?: array{label: string, url: string}, level?: string} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$eyebrow = (string) ($eyebrow ?? '');
$title   = (string) ($title ?? '');
$side    = (string) ($side ?? '');
$action  = is_array($action ?? null) ? $action : [];
$level   = (string) ($level ?? 'h2');
$level   = in_array($level, ['h1', 'h2', 'h3'], true) ? $level : 'h2';
?>
<div class="sk-headrow reveal">
    <div>
        <?php if ($eyebrow !== '') : ?>
            <p class="sk-eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <?php if ($title !== '') : ?>
            <<?php echo $level; ?>><?php echo wp_kses_post($title); ?></<?php echo $level; ?>>
        <?php endif; ?>
    </div>

    <?php if ($side !== '') : ?>
        <p class="sk-headrow__side"><?php echo wp_kses_post($side); ?></p>
    <?php endif; ?>

    <?php if (!empty($action['url'])) : ?>
        <a class="sk-btn sk-btn--outline sk-btn--sm" href="<?php echo esc_url((string) $action['url']); ?>">
            <?php echo esc_html((string) ($action['label'] ?? __('View all', 'site-kit'))); ?>
            <?php Util\icon('arrow-right'); ?>
        </a>
    <?php endif; ?>
</div>
