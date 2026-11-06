<?php
/**
 * Template: single event.
 *
 * Meta block (date, time, location), description, optional RSVP form and other
 * upcoming events.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

while (have_posts()) :
    the_post();

    $event        = get_post();
    $date         = Util\meta('sk_event_date', $event);
    $time         = Util\meta('sk_event_time', $event);
    $location     = Util\meta('sk_event_location', $event);
    $registration = Util\meta('sk_event_registration', $event) === '1';
    $future       = Util\is_future($date);
    $terms        = get_the_terms($event, 'event_type');
    $terms        = is_array($terms) ? $terms : [];
    $archive      = (string) get_post_type_archive_link('event');

    [$ok, $notice] = Util\form_notice([
        'registered' => __('Thanks — your registration reached us. We will be in touch with the details.', 'site-kit'),
        'invalid'    => __('Please check your name and e-mail address.', 'site-kit'),
        'nonce'      => __('The form has expired. Please send it again.', 'site-kit'),
        'bot'        => __('That looked automated. Please use the form manually.', 'site-kit'),
        'rate'       => __('Too many submissions. Please try again later.', 'site-kit'),
        'failed'     => __('The message could not be sent. Please try again later.', 'site-kit'),
        'generic'    => __('Something went wrong. Please try again later.', 'site-kit'),
    ]);

    $others = Util\posts('event', 3, [
        'post__not_in' => [$event->ID],
        'meta_key'     => 'sk_event_date',
        'orderby'      => 'meta_value',
        'order'        => 'DESC',
    ]);

    \SiteKit\Template\partial('header', ['page_key' => 'events']);
    ?>
    <main id="content">
        <?php
        Util\page_hero([
            'title'  => get_the_title(),
            'crumbs' => array_filter([
                ['label' => __('Events', 'site-kit'), 'url' => $archive],
                ['label' => get_the_title()],
            ]),
            'meta'   => $future && $date !== '' ? Util\long_date($date) : '',
        ]);
        ?>

        <section class="sk-section">
            <div class="sk-wrap">
                <div class="sk-split">
                    <div class="sk-split__media reveal">
                        <img src="<?php echo esc_url(Util\thumb($event, 'large')); ?>"
                             alt="<?php echo esc_attr(get_the_title()); ?>" width="900" height="640">
                    </div>

                    <div class="reveal" data-d="1">
                        <?php if ($terms) : ?>
                            <div class="sk-tags">
                                <?php foreach ($terms as $term) : ?>
                                    <span class="sk-tag"><?php echo esc_html($term->name); ?></span>
                                <?php endforeach; ?>
                                <?php if (!$future) : ?>
                                    <span class="sk-badge"><?php esc_html_e('Past event', 'site-kit'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <ul class="sk-facts">
                            <?php if ($date !== '') : ?>
                                <li>
                                    <?php Util\icon('calendar', ''); ?>
                                    <span><strong><?php esc_html_e('Date', 'site-kit'); ?></strong><?php echo esc_html(Util\long_date($date)); ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if ($time !== '') : ?>
                                <li>
                                    <?php Util\icon('clock', ''); ?>
                                    <span><strong><?php esc_html_e('Start', 'site-kit'); ?></strong><?php echo esc_html($time); ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if ($location !== '') : ?>
                                <li>
                                    <?php Util\icon('location', ''); ?>
                                    <span><strong><?php esc_html_e('Location', 'site-kit'); ?></strong><?php echo esc_html($location); ?></span>
                                </li>
                            <?php endif; ?>
                            <?php if ($future && $registration) : ?>
                                <li>
                                    <?php Util\icon('user-plus', ''); ?>
                                    <span><strong><?php esc_html_e('Registration', 'site-kit'); ?></strong><?php esc_html_e('Required — use the form below.', 'site-kit'); ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>

                        <div class="sk-prose">
                            <?php the_content(); ?>
                        </div>

                        <?php if ($archive !== '') : ?>
                            <p class="sk-backlink">
                                <a class="sk-btn sk-btn--outline sk-btn--sm" href="<?php echo esc_url($archive); ?>">
                                    <?php Util\icon('arrow-left', ''); ?>
                                    <?php esc_html_e('All events', 'site-kit'); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($future && $registration && Config\feature('rsvp', true)) : ?>
            <section class="sk-section sk-section--tint" id="rsvp">
                <div class="sk-wrap sk-wrap--narrow">
                    <?php Util\notice($ok, $notice); ?>

                    <form class="sk-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="sk_rsvp">
                        <input type="hidden" name="sk_event_id" value="<?php echo esc_attr((string) $event->ID); ?>">
                        <?php Util\form_security_fields('sk_rsvp'); ?>

                        <h2 class="sk-form__title"><?php esc_html_e('Register for this event', 'site-kit'); ?></h2>

                        <div class="sk-form__grid">
                            <div class="sk-field">
                                <label for="sk-rsvp-name"><?php esc_html_e('Name', 'site-kit'); ?> *</label>
                                <input id="sk-rsvp-name" name="sk_name" type="text" required autocomplete="name">
                            </div>
                            <div class="sk-field">
                                <label for="sk-rsvp-email"><?php esc_html_e('E-mail', 'site-kit'); ?> *</label>
                                <input id="sk-rsvp-email" name="sk_email" type="email" required autocomplete="email">
                            </div>
                            <div class="sk-field">
                                <label for="sk-rsvp-guests"><?php esc_html_e('Guests', 'site-kit'); ?></label>
                                <input id="sk-rsvp-guests" name="sk_guests" type="number" min="1" max="10" value="1">
                            </div>
                        </div>

                        <div class="sk-form__foot">
                            <button class="sk-btn sk-btn--accent" type="submit">
                                <?php esc_html_e('Send registration', 'site-kit'); ?>
                                <?php Util\icon('arrow-right', ''); ?>
                            </button>
                            <span class="sk-form__note"><?php esc_html_e('We only use your details to organise this event.', 'site-kit'); ?></span>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($others) : ?>
            <section class="sk-section">
                <div class="sk-wrap">
                    <?php
                    Util\head_row([
                        'eyebrow' => __('More', 'site-kit'),
                        'title'   => __('Other events', 'site-kit'),
                    ]);
                    ?>
                    <div class="sk-grid sk-grid--3">
                        <?php foreach ($others as $i => $other) : ?>
                            <?php \SiteKit\Template\partial('event-card', ['post' => $other, 'delay' => $i]); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <?php
    \SiteKit\Template\partial('footer');
endwhile;
