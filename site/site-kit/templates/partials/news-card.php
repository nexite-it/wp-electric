<?php
/**
 * Partial: news card.
 *
 * @var array{post?: \WP_Post, delay?: int, size?: string} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$item = $args['post'] ?? ($post ?? null);
if (!$item instanceof \WP_Post) {
    return;
}

$delay = (int) ($delay ?? 0);
$words = ($size ?? '') === 'large' ? 30 : 18;
?>
<article class="sk-card sk-news reveal" data-d="<?php echo esc_attr((string) $delay); ?>">
    <a class="sk-news__media" href="<?php the_permalink($item); ?>" tabindex="-1" aria-hidden="true">
        <img src="<?php echo esc_url(Util\thumb($item, ($size ?? '') === 'large' ? 'large' : 'medium_large')); ?>"
             alt="" loading="lazy" width="640" height="420">
    </a>
    <div class="sk-news__body">
        <p class="sk-news__meta">
            <time datetime="<?php echo esc_attr(Util\date_iso($item)); ?>"><?php echo esc_html(Util\date($item)); ?></time>
        </p>
        <h3><a href="<?php the_permalink($item); ?>"><?php echo esc_html(get_the_title($item)); ?></a></h3>
        <p><?php echo esc_html(Util\excerpt($item, $words)); ?></p>
        <span class="sk-card__foot">
            <?php esc_html_e('Read more', 'site-kit'); ?>
            <?php Util\icon('chevron-right', ''); ?>
        </span>
    </div>
</article>
