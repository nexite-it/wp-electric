<?php
/**
 * Partial: event card.
 *
 * Used by the events archive and the start page. Reads its data from the
 * configured meta keys, so it works for any post type with a date field.
 *
 * @var array{post?: \WP_Post, delay?: int} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$event = $args['post'] ?? ($post ?? null);
if (!$event instanceof \WP_Post) {
    return;
}

$delay    = (int) ($delay ?? 0);
$date     = Util\meta('sk_event_date', $event);
$time     = Util\meta('sk_event_time', $event);
$location = Util\meta('sk_event_location', $event);
$parts    = Util\date_parts($date);
$terms    = get_the_terms($event, 'event_type');
$terms    = is_array($terms) ? $terms : [];
$future   = Util\is_future($date);
$registration = Util\meta('sk_event_registration', $event) === '1';
?>
<article class="sk-card sk-event reveal" data-d="<?php echo esc_attr((string) $delay); ?>">
    <a class="sk-event__media" href="<?php the_permalink($event); ?>" tabindex="-1" aria-hidden="true">
        <img src="<?php echo esc_url(Util\thumb($event, 'medium_large', 'site/placeholder.svg')); ?>"
             alt="" loading="lazy" width="640" height="480">
        <?php if ($parts['day'] !== '') : ?>
            <span class="sk-event__date">
                <b><?php echo esc_html($parts['day']); ?></b>
                <span><?php echo esc_html($parts['month']); ?></span>
            </span>
        <?php endif; ?>
    </a>

    <div class="sk-event__body">
        <?php if ($location !== '' || $time !== '') : ?>
            <p class="sk-event__where">
                <?php if ($location !== '') : ?>
                    <?php Util\icon('location', ''); ?>
                    <?php echo esc_html($location); ?>
                <?php endif; ?>
                <?php if ($time !== '') : ?>
                    <span><?php echo esc_html($time); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <h3><a href="<?php the_permalink($event); ?>"><?php echo esc_html(get_the_title($event)); ?></a></h3>

        <p class="sk-event__text"><?php echo esc_html(Util\excerpt($event, 20)); ?></p>

        <div class="sk-tags">
            <?php foreach ($terms as $term) : ?>
                <span class="sk-tag"><?php echo esc_html($term->name); ?></span>
            <?php endforeach; ?>
            <?php if ($future && $registration) : ?>
                <span class="sk-badge sk-badge--accent"><?php esc_html_e('Registration', 'site-kit'); ?></span>
            <?php elseif (!$future) : ?>
                <span class="sk-badge"><?php esc_html_e('Past', 'site-kit'); ?></span>
            <?php endif; ?>
        </div>
    </div>
</article>
