<?php
/**
 * Partial: page hero (title band + breadcrumbs).
 *
 * @var array{title?: string, lead?: string, eyebrow?: string, crumbs?: array<int, array{label: string, url?: string}>, meta?: string} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$title   = (string) ($title ?? get_the_title());
$lead    = (string) ($lead ?? '');
$eyebrow = (string) ($eyebrow ?? '');
$meta    = (string) ($meta ?? '');
$crumbs  = isset($crumbs) && is_array($crumbs) ? $crumbs : [];
?>
<section class="sk-pagehero">
    <div class="sk-wrap">
        <?php if ($crumbs) : ?>
            <nav class="sk-crumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'site-kit'); ?>">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'site-kit'); ?></a>
                <?php foreach ($crumbs as $crumb) : ?>
                    <span aria-hidden="true">›</span>
                    <?php if (!empty($crumb['url'])) : ?>
                        <a href="<?php echo esc_url((string) $crumb['url']); ?>"><?php echo esc_html((string) ($crumb['label'] ?? '')); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html((string) ($crumb['label'] ?? '')); ?></span>
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
    </div>
</section>
