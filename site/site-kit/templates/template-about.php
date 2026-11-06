<?php
/**
 * Template: about page.
 *
 * Uses the page content if the editor wrote some, and falls back to the
 * configured organization description plus the focus areas, so the page is
 * never empty on a fresh install.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$intro    = Content\page_intro('about');
$has_text = trim((string) get_post_field('post_content', get_the_ID())) !== '';

\SiteKit\Template\partial('header', ['page_key' => 'about']);
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

    <section class="sk-section">
        <div class="sk-wrap">
            <div class="sk-split">
                <div class="reveal">
                    <p class="sk-eyebrow"><?php esc_html_e('Who we are', 'site-kit'); ?></p>

                    <?php if ($has_text) : ?>
                        <div class="sk-prose">
                            <?php the_content(); ?>
                        </div>
                    <?php else : ?>
                        <h2><?php echo esc_html((string) Config\get('brand.name', '')); ?></h2>
                        <p class="sk-lead"><?php echo esc_html((string) Config\get('brand.description', '')); ?></p>
                        <p><?php esc_html_e('Replace this text in the WordPress admin: open this page and write whatever describes your organization best. The layout stays the same.', 'site-kit'); ?></p>
                    <?php endif; ?>

                    <div class="sk-actions">
                        <?php $team = Config\resolve_location('team'); ?>
                        <?php if ($team !== '') : ?>
                            <a class="sk-btn sk-btn--primary" href="<?php echo esc_url($team); ?>">
                                <?php esc_html_e('Meet the team', 'site-kit'); ?>
                                <?php Util\icon('arrow-right'); ?>
                            </a>
                        <?php endif; ?>
                        <?php $contact = Config\resolve_location('contact'); ?>
                        <?php if ($contact !== '') : ?>
                            <a class="sk-btn sk-btn--outline" href="<?php echo esc_url($contact); ?>">
                                <?php esc_html_e('Get in touch', 'site-kit'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sk-split__media reveal" data-d="1">
                    <img src="<?php echo esc_url(Util\asset('img/site/feature.svg')); ?>"
                         alt="" loading="lazy" width="1200" height="800">
                </div>
            </div>
        </div>
    </section>

    <section class="sk-section sk-section--tint">
        <div class="sk-wrap">
            <?php
            Util\head_row([
                'eyebrow' => __('How we work', 'site-kit'),
                'title'   => __('Three principles', 'site-kit'),
            ]);
            ?>
            <div class="sk-grid sk-grid--3">
                <?php foreach (Content\principles() as $i => $principle) : ?>
                    <div class="sk-card sk-card--pad reveal" data-d="<?php echo esc_attr((string) $i); ?>">
                        <div class="sk-card__icon"><?php Util\icon('check', ''); ?></div>
                        <h3><?php echo esc_html($principle['title']); ?></h3>
                        <p><?php echo esc_html($principle['text']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="sk-section">
        <div class="sk-wrap">
            <?php
            Util\head_row([
                'eyebrow' => __('Our focus', 'site-kit'),
                'title'   => __('What we spend our time on', 'site-kit'),
            ]);
            ?>
            <div class="sk-grid sk-grid--2">
                <?php foreach (Content\pillars() as $i => $pillar) : ?>
                    <div class="sk-card sk-card--pad sk-pillar-card reveal" data-d="<?php echo esc_attr((string) ($i % 2)); ?>">
                        <div class="sk-card__icon"><?php Util\icon((string) $pillar['icon'], ''); ?></div>
                        <h3><?php echo esc_html($pillar['title']); ?></h3>
                        <ul class="sk-list">
                            <?php foreach ($pillar['points'] as $point) : ?>
                                <li><?php echo wp_kses_post($point); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
