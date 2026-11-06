<?php
/**
 * Template: Startseite.
 *
 * Built around the visitor's first questions, in this order:
 *   1. "Can someone help me now?"    → hero with emergency panel
 *   2. "Are they qualified?"          → trust bar (certifications, partners)
 *   3. "Do they do what I need?"      → service grid (high-ticket first)
 *   4. "Why them?"                    → figures and promises
 *   5. (and for craftspeople)         → recruiting banner
 *
 * All copy comes from inc/content.php, all business data from config.php.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$hero      = Content\hero();
$certs     = Content\certifications();
$why       = Content\why();
$stats     = Content\stats();
$jobs      = Content\jobs();
$emergency = Config\emergency();
$partners  = array_filter((array) Config\get('partners', []), 'is_array');
$whatsapp  = Util\whatsapp_url();
$hours     = Util\hours_summary();

$services = array_filter(Content\services(), static fn(array $s): bool => !empty($s['home']));
$extras   = array_diff_key(Content\services(), $services);

$contact_url  = Config\page_url('contact', [], 'anfrage');
$services_url = Config\page_url('services');
$about_url    = Config\page_url('about');

\SiteKit\Template\partial('header', ['page_key' => 'home']);
?>
<main id="content">

    <!-- Hero: who we are + immediate help -->
    <section class="sk-hero">
        <div class="sk-wrap sk-hero__grid">
            <div class="sk-hero__copy">
                <p class="sk-hero__kicker"><?php echo esc_html($hero['kicker']); ?></p>
                <h1 class="sk-hero__title">
                    <?php foreach ($hero['title'] as $i => $line) : ?>
                        <span class="<?php echo $i === count($hero['title']) - 1 ? 'sk-hero__accent' : 'sk-hero__line'; ?>"><?php echo esc_html($line); ?></span>
                    <?php endforeach; ?>
                </h1>
                <p class="sk-hero__lead"><?php echo esc_html($hero['lead']); ?></p>

                <div class="sk-hero__actions">
                    <?php if ($contact_url !== '') : ?>
                        <a class="sk-btn sk-btn--accent sk-btn--lg" href="<?php echo esc_url($contact_url); ?>">
                            <?php echo esc_html($hero['primary']); ?>
                            <?php Util\icon('arrow-right'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($emergency['enabled']) : ?>
                        <a class="sk-btn sk-btn--sos sk-btn--lg" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                            <span class="sk-live" aria-hidden="true"></span>
                            <span><?php esc_html_e('Notdienst', 'site-kit'); ?> <b><?php echo esc_html($emergency['phone']); ?></b></span>
                        </a>
                    <?php endif; ?>
                </div>

                <ul class="sk-hero__checks">
                    <?php foreach ($hero['checks'] as $check) : ?>
                        <li><?php Util\icon('check', ''); ?><?php echo esc_html($check); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if ($emergency['enabled']) : ?>
                <aside class="sk-ops" aria-label="<?php esc_attr_e('Notdienst und Erreichbarkeit', 'site-kit'); ?>">
                    <div class="sk-ops__head">
                        <span class="sk-label"><?php esc_html_e('Elektro-Notdienst', 'site-kit'); ?></span>
                        <span class="sk-ops__live"><span class="sk-live" aria-hidden="true"></span><?php esc_html_e('24/7 erreichbar', 'site-kit'); ?></span>
                    </div>

                    <a class="sk-ops__phone" href="<?php echo esc_url(Util\tel($emergency['phone']), ['tel']); ?>">
                        <?php Util\icon('phone', ''); ?>
                        <span><?php echo esc_html($emergency['phone']); ?></span>
                    </a>
                    <?php if ($emergency['note'] !== '') : ?>
                        <p class="sk-ops__note"><?php echo esc_html($emergency['note']); ?></p>
                    <?php endif; ?>

                    <dl class="sk-ops__rows">
                        <?php if ($hours !== '') : ?>
                            <div>
                                <dt><?php esc_html_e('Büro', 'site-kit'); ?></dt>
                                <dd class="sk-status" data-open-status>
                                    <span class="sk-status__dot" aria-hidden="true"></span>
                                    <span data-open-text><?php echo esc_html($hours); ?></span>
                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($emergency['eta'] !== '') : ?>
                            <div>
                                <dt><?php esc_html_e('Anfahrt', 'site-kit'); ?></dt>
                                <dd><?php echo esc_html($emergency['eta']); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt><?php esc_html_e('Gebiet', 'site-kit'); ?></dt>
                            <dd><?php esc_html_e('Bonn & Rhein-Sieg-Kreis', 'site-kit'); ?></dd>
                        </div>
                    </dl>

                    <p class="sk-ops__warn">
                        <?php Util\icon('alert', ''); ?>
                        <span><?php esc_html_e('Bei Brand oder Verletzten zuerst die 112 wählen. Betroffenen Stromkreis abschalten und Abstand halten.', 'site-kit'); ?></span>
                    </p>
                </aside>
            <?php endif; ?>
        </div>
    </section>

    <!-- Trust bar: qualifications + partner brands -->
    <section class="sk-trust" aria-label="<?php esc_attr_e('Qualifikationen und Partner', 'site-kit'); ?>">
        <div class="sk-wrap">
            <ul class="sk-trust__certs">
                <?php foreach ($certs as $cert) : ?>
                    <li class="sk-cert">
                        <span class="sk-cert__icon"><?php Util\icon((string) $cert['icon'], ''); ?></span>
                        <span class="sk-cert__text">
                            <b><?php echo esc_html($cert['title']); ?></b>
                            <small><?php echo esc_html($cert['text']); ?></small>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($partners) : ?>
                <div class="sk-trust__partners">
                    <p class="sk-label"><?php esc_html_e('Partner für Markentechnik', 'site-kit'); ?></p>
                    <ul class="sk-logos">
                        <?php foreach ($partners as $partner) : ?>
                            <li class="sk-logo">
                                <?php if (!empty($partner['logo'])) : ?>
                                    <img src="<?php echo esc_url(Util\img((string) $partner['logo'])); ?>" alt="<?php echo esc_attr((string) ($partner['name'] ?? '')); ?>" loading="lazy" height="28">
                                <?php else : ?>
                                    <?php echo esc_html((string) ($partner['name'] ?? '')); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Services -->
    <section class="sk-section" id="leistungen" aria-labelledby="sk-services-title">
        <div class="sk-wrap">
            <div class="sk-headrow reveal">
                <div>
                    <p class="sk-eyebrow"><?php esc_html_e('Leistungen', 'site-kit'); ?></p>
                    <h2 id="sk-services-title"><?php esc_html_e('Von der Wallbox bis zum Zählerschrank.', 'site-kit'); ?></h2>
                </div>
                <p class="sk-headrow__side"><?php esc_html_e('Vier Schwerpunkte, ein Ansprechpartner: Wir planen, installieren und prüfen – alles aus einer Hand.', 'site-kit'); ?></p>
            </div>

            <div class="sk-bento">
                <?php $n = 0; ?>
                <?php foreach ($services as $key => $service) : ?>
                    <?php
                    $n++;
                    $is_feature = $service['home'] === 'feature';
                    $url        = $services_url !== '' ? $services_url . '#' . $key : '';
                    ?>
                    <article class="sk-svc<?php echo $is_feature ? ' sk-svc--feature' : ''; ?> reveal" data-d="<?php echo esc_attr((string) (($n - 1) % 2)); ?>">
                        <div class="sk-svc__top">
                            <span class="sk-svc__icon"><?php Util\icon((string) $service['icon'], ''); ?></span>
                            <span class="sk-svc__num"><?php echo esc_html(sprintf('%02d', $n)); ?></span>
                        </div>
                        <h3 class="sk-svc__title">
                            <?php if ($url !== '') : ?>
                                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($service['title']); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($service['title']); ?>
                            <?php endif; ?>
                        </h3>
                        <p class="sk-svc__text"><?php echo esc_html($service['teaser']); ?></p>
                        <?php if (!empty($service['chips'])) : ?>
                            <ul class="sk-chips">
                                <?php foreach ($service['chips'] as $chip) : ?>
                                    <li><?php echo esc_html($chip); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <span class="sk-svc__more" aria-hidden="true">
                            <?php esc_html_e('Mehr erfahren', 'site-kit'); ?>
                            <?php Util\icon('arrow-right', ''); ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($services_url !== '') : ?>
                <p class="sk-more reveal">
                    <span>
                        <?php
                        echo esc_html(sprintf(
                            /* translators: %s: comma separated service names */
                            __('Außerdem: %s, E-Check, Zählerschrank-Erneuerung und Rauchwarnmelder.', 'site-kit'),
                            implode(', ', array_map(static fn(array $s): string => (string) $s['title'], $extras))
                        ));
                        ?>
                    </span>
                    <a class="sk-link" href="<?php echo esc_url($services_url); ?>">
                        <?php esc_html_e('Alle Leistungen', 'site-kit'); ?>
                        <?php Util\icon('arrow-right', ''); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Why us -->
    <section class="sk-section sk-section--tint" aria-labelledby="sk-why-title">
        <div class="sk-wrap sk-why">
            <div class="sk-why__copy reveal">
                <p class="sk-eyebrow"><?php echo esc_html($why['eyebrow']); ?></p>
                <h2 id="sk-why-title"><?php echo esc_html($why['title']); ?> <span class="sk-mark"><?php echo esc_html($why['mark']); ?></span></h2>
                <p class="sk-lead"><?php echo esc_html($why['text']); ?></p>
                <ul class="sk-checklist">
                    <?php foreach ($why['points'] as $point) : ?>
                        <li><?php Util\icon('check', ''); ?><span><?php echo esc_html($point); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($about_url !== '') : ?>
                    <a class="sk-link" href="<?php echo esc_url($about_url); ?>">
                        <?php esc_html_e('Mehr über uns', 'site-kit'); ?>
                        <?php Util\icon('arrow-right', ''); ?>
                    </a>
                <?php endif; ?>
            </div>

            <dl class="sk-figures reveal" data-d="1">
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

    <!-- Recruiting -->
    <section class="sk-jobs" aria-labelledby="sk-jobs-title">
        <div class="sk-wrap sk-jobs__inner">
            <div class="sk-jobs__copy reveal">
                <p class="sk-label"><?php echo esc_html($jobs['eyebrow']); ?></p>
                <h2 id="sk-jobs-title"><?php echo esc_html($jobs['title']); ?></h2>
                <p><?php echo esc_html($jobs['text']); ?></p>
                <div class="sk-actions">
                    <?php $apply = Config\page_url('contact', ['leistung' => 'bewerbung'], 'anfrage'); ?>
                    <?php if ($apply !== '') : ?>
                        <a class="sk-btn sk-btn--dark" href="<?php echo esc_url($apply); ?>">
                            <?php echo esc_html($jobs['apply']); ?>
                            <?php Util\icon('arrow-right'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($whatsapp !== '') : ?>
                        <a class="sk-btn sk-btn--outline-dark" href="<?php echo esc_url(Util\whatsapp_url($jobs['quick_text'])); ?>" rel="noopener" target="_blank">
                            <?php Util\icon('whatsapp'); ?>
                            <?php echo esc_html($jobs['quick']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <ul class="sk-jobs__list reveal" data-d="1">
                <?php foreach ($jobs['positions'] as $position) : ?>
                    <li>
                        <a href="<?php echo esc_url($about_url !== '' ? $about_url . '#karriere' : $apply); ?>">
                            <span>
                                <b><?php echo esc_html($position['title']); ?></b>
                                <small><?php echo esc_html($position['meta']); ?></small>
                            </span>
                            <?php Util\icon('arrow-right', ''); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

</main>
<?php
\SiteKit\Template\partial('footer');
