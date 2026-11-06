<?php
/**
 * Partial: person card.
 *
 * @var array{post?: \WP_Post, delay?: int} $args
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$person = $args['post'] ?? ($post ?? null);
if (!$person instanceof \WP_Post) {
    return;
}

$delay = (int) ($delay ?? 0);
$role  = Util\meta('sk_person_role', $person);
$email = Util\meta('sk_person_email', $person);
?>
<article class="sk-card sk-person reveal" data-d="<?php echo esc_attr((string) $delay); ?>">
    <div class="sk-person__media">
        <img src="<?php echo esc_url(Util\thumb($person, 'medium', 'site/placeholder.svg')); ?>"
             alt="<?php echo esc_attr(get_the_title($person)); ?>" loading="lazy" width="420" height="520">
    </div>
    <div class="sk-person__body">
        <h3><?php echo esc_html(get_the_title($person)); ?></h3>
        <?php if ($role !== '') : ?>
            <span class="sk-person__role"><?php echo esc_html($role); ?></span>
        <?php endif; ?>
        <?php if ($email !== '') : ?>
            <a class="sk-person__mail" href="mailto:<?php echo esc_attr($email); ?>"><?php Util\icon('mail', ''); ?><?php echo esc_html($email); ?></a>
        <?php endif; ?>
    </div>
</article>
