<?php
/**
 * Template: Über uns.
 *
 * Local authority in three blocks: the Meisterbetrieb intro (split screen
 * with team photo), the three values, and open positions (#karriere).
 *
 * Editable without code:
 *   - page content in the editor replaces the intro paragraphs
 *   - the page's featured image replaces the team photo placeholder
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

if (have_posts()) {
    the_post();
}

$intro    = Content\page_intro('about');
$about    = Content\about();
$values   = Content\values();
$jobs     = Content\jobs();
$stats    = Content\stats();
$towns    = array_values(array_filter(array_map('strval', (array) Config\get('service_area', []))));
$founded  = (int) Config\get('brand.founded', 0);
$has_text = trim((string) get_post_field('post_content', get_the_ID())) !== '';
$photo    = has_post_thumbnail() ? (string) get_the_post_thumbnail_url(null, 'large') : Util\img('site/team.svg');
$apply    = Config\page_url('contact', ['leistung' => 'bewerbung'], 'anfrage');
$whatsapp = Util\whatsapp_url($jobs['quick_text']);

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => get_the_title() ?: $intro['eyebrow']]],
    ]);
    ?>

    <!-- Meisterbetrieb intro -->
    <section class="sk-section" aria-labelledby="sk-about-title">
        <div class="sk-wrap">
            <div class="sk-split sk-split--about">
                <figure class="sk-photo reveal">
                    <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($about['photo_alt']); ?>" width="1200" height="1000" loading="lazy">
                    <?php if ($founded > 0) : ?>
                        <figcaption class="sk-photo__badge">
                            <b><?php echo esc_html(sprintf(__('Seit %d', 'site-kit'), $founded)); ?></b>
                            <span><?php echo esc_html($about['badge']); ?></span>
                        </figcaption>
                    <?php endif; ?>
                </figure>

                <div class="sk-split__body reveal" data-d="1">
                    <p class="sk-eyebrow"><?php echo esc_html($about['eyebrow']); ?></p>
                    <h2 id="sk-about-title"><?php echo esc_html($about['title']); ?></h2>

                    <?php if ($has_text) : ?>
                        <div class="sk-prose"><?php the_content(); ?></div>
                    <?php else : ?>
                        <?php foreach ($about['paragraphs'] as $i => $paragraph) : ?>
                            <p<?php echo $i === 0 ? ' class="sk-lead"' : ''; ?>><?php echo esc_html($paragraph); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <p class="sk-signature">
                        <b><?php echo esc_html($about['signature']); ?></b>
                        <span><?php echo esc_html($about['role']); ?></span>
                    </p>

                    <?php if ($towns) : ?>
                        <div class="sk-area">
                            <p class="sk-label"><?php esc_html_e('Einsatzgebiet', 'site-kit'); ?></p>
                            <ul class="sk-chips sk-chips--light">
                                <?php foreach ($towns as $town) : ?>
                                    <li><?php echo esc_html($town); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="sk-figures sk-figures--row reveal">
                <?php foreach ($stats as $stat) : ?>
                    <div class="sk-figure">
                        <dt><?php echo esc_html($stat['label']); ?></dt>
                        <dd>
                            <span data-count="<?php echo esc_attr((string) (int) $stat['num']); ?>"><?php echo esc_html(number_format((int) $stat['num'], 0, ',', '.')); ?></span><?php echo esc_html($stat['suffix']); ?>
                        </dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </section>

    <!-- Values -->
    <section class="sk-section sk-section--tint" aria-labelledby="sk-values-title">
        <div class="sk-wrap">
            <div class="sk-headrow reveal">
                <div>
                    <p class="sk-eyebrow"><?php esc_html_e('Unsere Werte', 'site-kit'); ?></p>
                    <h2 id="sk-values-title"><?php esc_html_e('Worauf Sie sich verlassen können.', 'site-kit'); ?></h2>
                </div>
            </div>

            <div class="sk-values">
                <?php foreach ($values as $i => $value) : ?>
                    <article class="sk-value reveal" data-d="<?php echo esc_attr((string) $i); ?>">
                        <div class="sk-value__top">
                            <span class="sk-value__icon"><?php Util\icon((string) $value['icon'], ''); ?></span>
                            <span class="sk-value__num"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
                        </div>
                        <h3><?php echo esc_html($value['title']); ?></h3>
                        <p><?php echo esc_html($value['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Careers -->
    <section class="sk-section" id="karriere" aria-labelledby="sk-career-title">
        <div class="sk-wrap sk-career">
            <div class="reveal">
                <p class="sk-eyebrow"><?php echo esc_html($jobs['eyebrow']); ?></p>
                <h2 id="sk-career-title"><?php echo esc_html($jobs['title']); ?></h2>
                <p class="sk-lead"><?php echo esc_html($jobs['text']); ?></p>
                <ul class="sk-checklist">
                    <?php foreach ($jobs['benefits'] as $benefit) : ?>
                        <li><?php Util\icon('check', ''); ?><span><?php echo esc_html($benefit); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="sk-actions">
                    <?php if ($apply !== '') : ?>
                        <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($apply); ?>">
                            <?php echo esc_html($jobs['apply']); ?>
                            <?php Util\icon('arrow-right'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($whatsapp !== '') : ?>
                        <a class="sk-btn sk-btn--outline" href="<?php echo esc_url($whatsapp); ?>" rel="noopener" target="_blank">
                            <?php Util\icon('whatsapp'); ?>
                            <?php echo esc_html($jobs['quick']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sk-positions reveal" data-d="1">
                <p class="sk-label"><?php esc_html_e('Offene Stellen', 'site-kit'); ?></p>
                <ul>
                    <?php foreach ($jobs['positions'] as $position) : ?>
                        <li>
                            <a href="<?php echo esc_url($apply); ?>">
                                <span class="sk-positions__icon"><?php Util\icon('helmet', ''); ?></span>
                                <span>
                                    <b><?php echo esc_html($position['title']); ?></b>
                                    <small><?php echo esc_html($position['meta']); ?></small>
                                </span>
                                <?php Util\icon('arrow-right', ''); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="sk-positions__note"><?php esc_html_e('Keine passende Stelle dabei? Wir freuen uns auch über Initiativbewerbungen.', 'site-kit'); ?></p>
            </div>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
