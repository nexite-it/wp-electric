<?php
/**
 * Partial: page hero (title band + breadcrumbs) for every inner page.
 *
 * @var array{title?: string, lead?: string, eyebrow?: string, crumbs?: array<int, array{label: string, url?: string}>, meta?: string, actions?: array<int, array{label: string, url: string, style?: string, icon?: string}>} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$title   = (string) ($title ?? get_the_title());
$lead    = (string) ($lead ?? '');
$eyebrow = (string) ($eyebrow ?? '');
$meta    = (string) ($meta ?? '');
$crumbs  = isset($crumbs) && is_array($crumbs) ? $crumbs : [];
$actions = isset($actions) && is_array($actions) ? array_filter($actions, static fn($a): bool => !empty($a['url'])) : [];
?>
<section class="sk-pagehero">
    <div class="sk-wrap">
        <?php if ($crumbs) : ?>
            <nav class="sk-crumbs" aria-label="<?php esc_attr_e('Brotkrümelnavigation', 'site-kit'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Startseite', 'site-kit'); ?></a>
                <?php foreach ($crumbs as $crumb) : ?>
                    <span class="sk-crumbs__sep" aria-hidden="true">/</span>
                    <?php if (!empty($crumb['url'])) : ?>
                        <a href="<?php echo esc_url((string) $crumb['url']); ?>"><?php echo esc_html((string) ($crumb['label'] ?? '')); ?></a>
                    <?php else : ?>
                        <span aria-current="page"><?php echo esc_html((string) ($crumb['label'] ?? '')); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($eyebrow !== '') : ?>
            <p class="sk-eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>

        <h1><?php echo esc_html($title); ?></h1>

        <?php if ($lead !== '') : ?>
            <p class="sk-lead"><?php echo esc_html($lead); ?></p>
        <?php endif; ?>

        <?php if ($meta !== '') : ?>
            <p class="sk-pagehero__meta"><?php echo esc_html($meta); ?></p>
        <?php endif; ?>

        <?php if ($actions) : ?>
            <div class="sk-actions">
                <?php foreach ($actions as $action) : ?>
                    <?php $btn = 'sk-btn sk-btn--' . sanitize_html_class((string) ($action['style'] ?? 'accent')); ?>
                    <a class="<?php echo esc_attr($btn); ?>" href="<?php echo esc_url((string) $action['url'], ['http', 'https', 'tel', 'mailto']); ?>">
                        <?php if (!empty($action['icon'])) : ?>
                            <?php Util\icon((string) $action['icon']); ?>
                        <?php endif; ?>
                        <?php echo esc_html((string) $action['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
