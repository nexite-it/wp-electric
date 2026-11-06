<?php
/**
 * Template: generic page.
 *
 * Used for any page without an assigned template, and as the fallback for
 * post types without a dedicated single template.
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

while (have_posts()) :
    the_post();
    $title = get_the_title();

    \SiteKit\Template\partial('header', []);
    ?>
    <main id="content">
        <?php
        Util\page_hero([
            'title'  => $title,
            'crumbs' => $title !== '' ? [['label' => $title]] : [],
        ]);
        ?>

        <section class="sk-section">
            <div class="sk-wrap sk-wrap--narrow">
                <article class="sk-prose reveal">
                    <?php the_content(); ?>
                </article>
            </div>
        </section>
    </main>
    <?php
    \SiteKit\Template\partial('footer');
endwhile;
