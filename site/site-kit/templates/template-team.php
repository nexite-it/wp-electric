<?php
/**
 * Template: team page.
 *
 * Everyone from the `person` post type, grouped by the `sk_person_group` meta
 * field. Groups that are empty are not rendered, so adding a new group in the
 * admin is enough to make it appear.
 */

defined('ABSPATH') || exit;

use SiteKit\Content;
use SiteKit\Util;

$intro = Content\page_intro('team');

$people = Util\posts('person', -1, [
    'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
]);

$groups = [];
foreach ($people as $person) {
    $group = Util\meta('sk_person_group', $person, 'team');
    $groups[$group][] = $person;
}

$labels = [
    'board'   => __('Board', 'site-kit'),
    'team'    => __('Team', 'site-kit'),
    'alumni'  => __('Alumni', 'site-kit'),
];

\SiteKit\Template\partial('header', ['page_key' => 'team']);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => get_the_title() ?: $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => $intro['title']]],
    ]);
    ?>

    <?php if ($groups) : ?>
        <?php foreach ($groups as $group => $members) : ?>
            <section class="sk-section">
                <div class="sk-wrap">
                    <?php
                    Util\head_row([
                        'eyebrow' => __('People', 'site-kit'),
                        'title'   => $labels[$group] ?? ucfirst((string) $group),
                    ]);
                    ?>
                    <div class="sk-grid sk-grid--4">
                        <?php foreach ($members as $i => $person) : ?>
                            <?php \SiteKit\Template\partial('person-card', ['post' => $person, 'delay' => $i % 4]); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else : ?>
        <section class="sk-section">
            <div class="sk-wrap">
                <div class="sk-empty reveal">
                    <div class="sk-empty__icon"><?php Util\icon('users', ''); ?></div>
                    <h2><?php esc_html_e('No people yet', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Add a person in the WordPress admin and they will show up here automatically.', 'site-kit'); ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php $content = trim((string) get_post_field('post_content', get_the_ID())); ?>
    <?php if ($content !== '') : ?>
        <section class="sk-section sk-section--tint">
            <div class="sk-wrap sk-wrap--narrow sk-prose">
                <?php the_content(); ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
\SiteKit\Template\partial('footer');
