<?php
/**
 * Template: start page.
 *
 * Hero, numbers band, topics marquee, what-we-do, upcoming events, news,
 * focus areas, team teaser, call to action and social strip.
 *
 * Sections that have no content are skipped, so a brand-new install with demo
 * content disabled still renders a clean page.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$hero     = Content\hero();
$stats    = Content\stats();
$topics   = Content\topics();
$pillars  = Content\pillars();
$events   = Util\upcoming_events(4);
$news     = Util\posts('news', 3);
$team     = Util\posts('person', 4, ['orderby' => 'menu_order', 'order' => 'ASC']);
$contact  = Config\contact();
$socials  = Config\socials();

$link = static function (array $target): string {
    return Config\resolve_location((string) ($target['slug'] ?? ''));
};

$primary_url   = $link($hero['primary']);
$secondary_url = $link($hero['secondary']);

\SiteKit\Template\partial('header', ['page_key' => 'home']);
?>
<main id="content">

    <!-- Hero -->
    <section class="sk-hero">
        <div class="sk-wrap">
            <div class="sk-hero__grid">
                <div class="sk-hero__copy">
                    <p class="sk-hero__kicker"><?php echo esc_html($hero['kicker']); ?></p>
                    <h1 class="sk-hero__title">
                        <?php foreach ($hero['title'] as $i => $line) : ?>
                            <span class="sk-box<?php echo $i === count($hero['title']) - 1 ? ' sk-box--accent' : ''; ?>"><?php echo esc_html($line); ?></span>
                        <?php endforeach; ?>
                    </h1>
                    <p class="sk-hero__lead"><?php echo esc_html($hero['lead']); ?></p>
                    <div class="sk-hero__actions">
                        <?php if ($primary_url !== '') : ?>
                            <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($primary_url); ?>">
                                <?php echo esc_html($hero['primary']['label']); ?>
                                <?php Util\icon('arrow-right'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($secondary_url !== '') : ?>
                            <a class="sk-btn sk-btn--outline" href="<?php echo esc_url($secondary_url); ?>">
                                <?php echo esc_html($hero['secondary']['label']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sk-hero__media">
                    <span class="sk-hero__dot" aria-hidden="true"></span>
                    <figure class="sk-hero__photo">
                        <img src="<?php echo esc_url(Util\asset('img/site/hero.svg')); ?>"
                             alt="" width="1200" height="900" fetchpriority="high">
                    </figure>

                    <?php if ($events['items']) :
                        $next = $events['items'][0];
                        $date = Util\meta('sk_event_date', $next);
                        $parts = Util\date_parts($date);
                        ?>
                        <a class="sk-hero__note" href="<?php the_permalink($next); ?>">
                            <span class="sk-hero__note-date">
                                <b><?php echo esc_html($parts['day']); ?></b>
                                <span><?php echo esc_html($parts['month']); ?></span>
                            </span>
                            <span>
                                <small><?php echo $events['upcoming'] ? esc_html__('Next event', 'site-kit') : esc_html__('Last event', 'site-kit'); ?></small>
                                <strong><?php echo esc_html(get_the_title($next)); ?></strong>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($stats) : ?>
                <div class="sk-stats">
                    <?php foreach ($stats as $stat) : ?>
                        <div class="sk-stats__item">
                            <div class="sk-stats__num" data-count="<?php echo esc_attr((string) $stat['num']); ?>"><?php echo esc_html((string) $stat['num']); ?></div>
                            <div class="sk-stats__label"><?php echo esc_html($stat['label']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($topics) : ?>
        <div class="sk-marquee" role="presentation">
            <div class="sk-marquee__track">
                <?php for ($i = 0; $i < 2; $i++) : ?>
                    <div class="sk-marquee__group"<?php echo $i ? ' aria-hidden="true"' : ''; ?>>
                        <?php foreach ($topics as $topic) : ?>
                            <span class="sk-marquee__item"><?php echo esc_html($topic); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- What we do -->
    <section class="sk-section">
        <div class="sk-wrap">
            <div class="sk-statement">
                <div class="reveal">
                    <p class="sk-eyebrow"><?php esc_html_e('What we do', 'site-kit'); ?></p>
                    <h2 class="sk-statement__title">
                        <?php esc_html_e('Talk less.', 'site-kit'); ?><br>
                        <span class="sk-mark"><?php esc_html_e('Do more.', 'site-kit'); ?></span>
                    </h2>
                </div>
                <div class="sk-statement__body reveal" data-d="1">
                    <p class="sk-lead">
                        <?php echo esc_html((string) Config\get('brand.description', '')); ?>
                    </p>
                    <?php $about = Config\resolve_location('about'); ?>
                    <?php if ($about !== '') : ?>
                        <a class="sk-link" href="<?php echo esc_url($about); ?>">
                            <?php esc_html_e('More about us', 'site-kit'); ?>
                            <?php Util\icon('arrow-right', ''); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sk-feature">
                <figure class="sk-feature__img reveal">
                    <img src="<?php echo esc_url(Util\asset('img/site/feature.svg')); ?>"
                         alt="" loading="lazy" width="1200" height="800">
                </figure>
                <div class="reveal" data-d="1">
                    <ol class="sk-principles">
                        <?php foreach (Content\principles() as $principle) : ?>
                            <li>
                                <b><?php echo esc_html($principle['title']); ?></b>
                                <span><?php echo esc_html($principle['text']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Events -->
    <?php if ($events['items']) : ?>
        <section class="sk-section sk-section--tint">
            <div class="sk-wrap">
                <?php
                Util\head_row([
                    'eyebrow' => $events['upcoming'] ? __('Calendar', 'site-kit') : __('Calendar', 'site-kit'),
                    'title'   => $events['upcoming'] ? __('What is coming up', 'site-kit') : __('Recently at our place', 'site-kit'),
                    'action'  => post_type_exists('event')
                        ? ['label' => __('All events', 'site-kit'), 'url' => (string) get_post_type_archive_link('event')]
                        : [],
                ]);
                ?>
                <ol class="sk-agenda reveal">
                    <?php foreach ($events['items'] as $event) :
                        $date     = Util\meta('sk_event_date', $event);
                        $time     = Util\meta('sk_event_time', $event);
                        $location = Util\meta('sk_event_location', $event);
                        $parts    = Util\date_parts($date);
                        $terms    = get_the_terms($event, 'event_type');
                        $terms    = is_array($terms) ? $terms : [];
                        ?>
                        <li>
                            <a class="sk-agenda__row" href="<?php the_permalink($event); ?>">
                                <span class="sk-agenda__date">
                                    <?php if ($parts['day'] !== '') : ?>
                                        <b><?php echo esc_html($parts['day']); ?></b>
                                        <span><?php echo esc_html($parts['month'] . ' ' . $parts['year']); ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="sk-agenda__main">
                                    <?php if ($terms) : ?>
                                        <span class="sk-agenda__cat"><?php echo esc_html($terms[0]->name); ?></span>
                                    <?php endif; ?>
                                    <span class="sk-agenda__title"><?php echo esc_html(get_the_title($event)); ?></span>
                                </span>
                                <span class="sk-agenda__where">
                                    <?php
                                    if ($location !== '') {
                                        Util\icon('location', '');
                                        echo esc_html($location);
                                    }
                                    if ($time !== '') {
                                        echo ' · ' . esc_html($time);
                                    }
                                    ?>
                                </span>
                                <span class="sk-agenda__arrow" aria-hidden="true"><?php Util\icon('arrow-right', ''); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
    <?php endif; ?>

    <!-- News -->
    <?php if ($news) : ?>
        <section class="sk-section">
            <div class="sk-wrap">
                <?php
                Util\head_row([
                    'eyebrow' => __('Updates', 'site-kit'),
                    'title'   => __('Latest news', 'site-kit'),
                    'action'  => ['label' => __('All news', 'site-kit'), 'url' => (string) get_post_type_archive_link('news')],
                ]);
                ?>
                <div class="sk-grid sk-grid--3">
                    <?php foreach ($news as $i => $item) : ?>
                        <?php \SiteKit\Template\partial('news-card', ['post' => $item, 'delay' => $i, 'size' => $i === 0 ? 'large' : '']); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Focus areas -->
    <section class="sk-section sk-section--dark">
        <div class="sk-wrap">
            <?php
            Util\head_row([
                'eyebrow' => __('Our focus', 'site-kit'),
                'title'   => __('Four areas. One goal: <span class="sk-mark">a better place.</span>', 'site-kit'),
            ]);
            ?>
            <div class="sk-pillars">
                <?php foreach ($pillars as $i => $pillar) : ?>
                    <div class="sk-pillar reveal" data-d="<?php echo esc_attr((string) $i); ?>">
                        <span class="sk-pillar__num"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
                        <div class="sk-pillar__icon"><?php Util\icon((string) $pillar['icon'], ''); ?></div>
                        <h3><?php echo esc_html($pillar['title']); ?></h3>
                        <ul>
                            <?php foreach ($pillar['points'] as $point) : ?>
                                <li><?php echo wp_kses_post($point); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Team teaser -->
    <?php if ($team) : ?>
        <section class="sk-section">
            <div class="sk-wrap">
                <?php
                Util\head_row([
                    'eyebrow' => __('Who we are', 'site-kit'),
                    'title'   => __('The people behind it', 'site-kit'),
                    'side'    => __('Every role here is voluntary.', 'site-kit'),
                    'action'  => Config\resolve_location('team') !== ''
                        ? ['label' => __('Meet the team', 'site-kit'), 'url' => Config\resolve_location('team')]
                        : [],
                ]);
                ?>
                <div class="sk-grid sk-grid--4">
                    <?php foreach ($team as $i => $person) : ?>
                        <?php \SiteKit\Template\partial('person-card', ['post' => $person, 'delay' => $i % 4]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Call to action -->
    <?php if ($primary_url !== '') : ?>
        <section class="sk-section sk-section--tight">
            <div class="sk-wrap">
                <div class="sk-cta reveal">
                    <h2><?php esc_html_e('Want to be part of it?', 'site-kit'); ?></h2>
                    <p><?php echo esc_html((string) Config\get('brand.description', '')); ?></p>
                    <div class="sk-cta__actions">
                        <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($primary_url); ?>">
                            <?php echo esc_html($hero['primary']['label']); ?>
                            <?php Util\icon('arrow-right'); ?>
                        </a>
                        <?php if ($secondary_url !== '') : ?>
                            <a class="sk-btn sk-btn--outline" href="<?php echo esc_url($secondary_url); ?>">
                                <?php echo esc_html($hero['secondary']['label']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

</main>
<?php
\SiteKit\Template\partial('footer');
