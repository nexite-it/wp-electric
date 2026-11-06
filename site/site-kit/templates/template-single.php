<?php
/**
 * Template: single news item.
 *
 * Also used for ordinary blog posts (the fallback single template).
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

while (have_posts()) :
    the_post();

    $item    = get_post();
    $type    = (string) get_post_type($item);
    $archive = (string) get_post_type_archive_link($type);
    $label   = $type === 'post' ? __('Beiträge', 'site-kit') : __('Aktuelles', 'site-kit');

    $related = Util\posts($type, 3, ['post__not_in' => [$item->ID]]);

    \SiteKit\Template\partial('header', []);
    ?>
    <main id="content">
        <?php
        Util\page_hero([
            'title'  => get_the_title(),
            'crumbs' => array_filter([
                ['label' => $label, 'url' => $archive],
                ['label' => get_the_title()],
            ]),
            'meta'   => Util\date($item),
        ]);
        ?>

        <section class="sk-section">
            <div class="sk-wrap sk-wrap--narrow">
                <figure class="sk-single__media reveal">
                    <img src="<?php echo esc_url(Util\thumb($item, 'large')); ?>"
                         alt="" width="1200" height="700">
                </figure>

                <article class="sk-prose reveal">
                    <?php the_content(); ?>
                </article>

                <?php if ($archive !== '') : ?>
                    <p class="sk-backlink">
                        <a class="sk-btn sk-btn--outline sk-btn--sm" href="<?php echo esc_url($archive); ?>">
                            <?php Util\icon('arrow-left', ''); ?>
                            <?php echo esc_html(sprintf(__('Zurück zu %s', 'site-kit'), $label)); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($related) : ?>
            <section class="sk-section sk-section--tint">
                <div class="sk-wrap">
                    <?php
                    Util\head_row([
                        'eyebrow' => __('Mehr', 'site-kit'),
                        'title'   => __('Weiterlesen', 'site-kit'),
                    ]);
                    ?>
                    <div class="sk-grid sk-grid--3">
                        <?php foreach ($related as $i => $other) : ?>
                            <?php \SiteKit\Template\partial('news-card', ['post' => $other, 'delay' => $i]); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <?php
    \SiteKit\Template\partial('footer');
endwhile;
