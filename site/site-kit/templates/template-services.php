<?php
/**
 * Template: Leistungen.
 *
 * SEO landing page for every service: one Z-pattern row per service (text
 * and image alternate sides), each with its own anchor (#wallbox, …), a
 * checklist, a spec sheet and a call to action that preselects the service
 * in the contact form. High-demand services (`focus` in content.php) get a
 * highlighted row. Process, FAQ and a closing call to action follow.
 *
 * Text written in the page editor is shown above the service rows.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

if (have_posts()) {
    the_post();
}

$intro     = Content\page_intro('services');
$services  = Content\services();
$process   = Content\process();
$faq       = Content\faq();
$contact   = Config\contact();
$emergency = Config\emergency();
$has_text  = trim((string) get_post_field('post_content', get_the_ID())) !== '';

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => get_the_title() ?: $intro['eyebrow']]],
        'actions' => [
            ['label' => __('Projekt anfragen', 'site-kit'), 'url' => Config\page_url('contact', [], 'anfrage'), 'style' => 'accent', 'icon' => 'arrow-right'],
            ['label' => sprintf(__('Anrufen: %s', 'site-kit'), $contact['phone']), 'url' => $contact['phone'] !== '' ? Util\tel($contact['phone']) : '', 'style' => 'ghost', 'icon' => 'phone'],
        ],
    ]);
    ?>

    <nav class="sk-subnav" aria-label="<?php esc_attr_e('Leistungen auf dieser Seite', 'site-kit'); ?>">
        <div class="sk-wrap">
            <ul class="sk-subnav__list">
                <?php foreach ($services as $key => $service) : ?>
                    <li>
                        <a href="#<?php echo esc_attr($key); ?>">
                            <?php Util\icon((string) $service['icon'], ''); ?>
                            <?php echo esc_html($service['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <?php if ($has_text) : ?>
        <section class="sk-section sk-section--tight">
            <div class="sk-wrap sk-wrap--narrow">
                <div class="sk-prose reveal"><?php the_content(); ?></div>
            </div>
        </section>
    <?php endif; ?>

    <div class="sk-zrows">
        <?php $n = 0; ?>
        <?php foreach ($services as $key => $service) : ?>
            <?php
            $n++;
            $num   = sprintf('%02d', $n);
            $focus = !empty($service['focus']);
            $cta   = Config\page_url('contact', ['leistung' => $key], 'anfrage');
            ?>
            <section class="sk-zrow<?php echo $focus ? ' sk-zrow--focus' : ''; ?>" id="<?php echo esc_attr($key); ?>" aria-labelledby="sk-svc-<?php echo esc_attr($key); ?>">
                <div class="sk-wrap sk-zrow__grid">
                    <figure class="sk-zrow__media reveal">
                        <img src="<?php echo esc_url(Util\img((string) $service['image'])); ?>"
                             alt="" width="1200" height="900" loading="lazy">
                        <?php if ($focus) : ?>
                            <span class="sk-zrow__badge"><?php Util\icon('zap', ''); ?><?php esc_html_e('Stark nachgefragt', 'site-kit'); ?></span>
                        <?php endif; ?>
                        <figcaption class="sk-label"><?php echo esc_html(sprintf(__('Abb. %1$s — %2$s', 'site-kit'), $num, $service['title'])); ?></figcaption>
                    </figure>

                    <div class="sk-zrow__body reveal" data-d="1">
                        <p class="sk-eyebrow"><?php echo esc_html($num . ' / ' . $service['kicker']); ?></p>
                        <h2 id="sk-svc-<?php echo esc_attr($key); ?>"><?php echo esc_html($service['headline']); ?></h2>
                        <p class="sk-lead"><?php echo esc_html($service['lead']); ?></p>

                        <ul class="sk-checklist sk-checklist--cols">
                            <?php foreach ($service['points'] as $point) : ?>
                                <li><?php Util\icon('check', ''); ?><span><?php echo esc_html($point); ?></span></li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if (!empty($service['specs'])) : ?>
                            <dl class="sk-specs">
                                <?php foreach ($service['specs'] as $label => $value) : ?>
                                    <div>
                                        <dt><?php echo esc_html((string) $label); ?></dt>
                                        <dd><?php echo esc_html((string) $value); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>

                        <div class="sk-actions">
                            <?php if ($cta !== '') : ?>
                                <a class="sk-btn sk-btn--accent" href="<?php echo esc_url($cta); ?>">
                                    <?php echo esc_html($service['cta']); ?>
                                    <?php Util\icon('arrow-right'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($contact['phone'] !== '') : ?>
                                <a class="sk-btn sk-btn--outline" href="<?php echo esc_url(Util\tel($contact['phone']), ['tel']); ?>">
                                    <?php Util\icon('phone'); ?>
                                    <?php echo esc_html($contact['phone']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Process -->
    <section class="sk-section sk-section--dark" aria-labelledby="sk-process-title">
        <div class="sk-wrap">
            <div class="sk-headrow reveal">
                <div>
                    <p class="sk-eyebrow"><?php esc_html_e('So arbeiten wir', 'site-kit'); ?></p>
                    <h2 id="sk-process-title"><?php esc_html_e('In vier Schritten zum fertigen Projekt.', 'site-kit'); ?></h2>
                </div>
                <p class="sk-headrow__side"><?php esc_html_e('Klare Abläufe, feste Ansprechpartner und ein Angebot, das hält, was es verspricht.', 'site-kit'); ?></p>
            </div>

            <ol class="sk-steps">
                <?php foreach ($process as $i => $step) : ?>
                    <li class="sk-step reveal" data-d="<?php echo esc_attr((string) min(3, $i)); ?>">
                        <span class="sk-step__num"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
                        <h3><?php echo esc_html($step['title']); ?></h3>
                        <p><?php echo esc_html($step['text']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- FAQ -->
    <?php if ($faq) : ?>
        <section class="sk-section" aria-labelledby="sk-faq-title">
            <div class="sk-wrap sk-faqwrap">
                <div class="reveal">
                    <p class="sk-eyebrow"><?php esc_html_e('Häufige Fragen', 'site-kit'); ?></p>
                    <h2 id="sk-faq-title"><?php esc_html_e('Kurz beantwortet.', 'site-kit'); ?></h2>
                    <p class="sk-lead"><?php esc_html_e('Ihre Frage ist nicht dabei? Rufen Sie uns an – wir beraten Sie gern, auch wenn es noch kein konkretes Projekt gibt.', 'site-kit'); ?></p>
                </div>
                <div class="sk-faq">
                    <?php foreach ($faq as $i => $item) : ?>
                        <details class="sk-faq__item reveal"<?php echo $i === 0 ? ' open' : ''; ?>>
                            <summary><?php echo esc_html($item['q']); ?></summary>
                            <p><?php echo esc_html($item['a']); ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Closing call to action -->
    <section class="sk-section sk-section--tight">
        <div class="sk-wrap">
            <div class="sk-cta reveal">
                <div>
                    <h2><?php esc_html_e('Ihr Vorhaben ist nicht dabei?', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Von der Außenbeleuchtung bis zur Netzwerkverkabelung: Fragen Sie uns einfach. Wenn wir es nicht selbst machen, sagen wir Ihnen, wer es gut macht.', 'site-kit'); ?></p>
                </div>
                <div class="sk-cta__actions">
                    <?php $any = Config\page_url('contact', ['leistung' => 'sonstiges'], 'anfrage'); ?>
                    <?php if ($any !== '') : ?>
                        <a class="sk-btn sk-btn--dark" href="<?php echo esc_url($any); ?>">
                            <?php esc_html_e('Anfrage stellen', 'site-kit'); ?>
                            <?php Util\icon('arrow-right'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($emergency['enabled']) : ?>
                        <a class="sk-btn sk-btn--outline-dark" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                            <?php Util\icon('phone'); ?>
                            <?php echo esc_html($emergency['label'] . ': ' . $emergency['phone']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
