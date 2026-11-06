<?php
/**
 * Template: privacy policy.
 *
 * Placeholder legal page. The notes below describe what this kit actually does
 * with data, so the text starts from the truth instead of boilerplate.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$intro   = Content\page_intro('privacy');
$contact = Config\contact();

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => get_the_title() ?: $intro['title'],
        'crumbs'  => [['label' => $intro['title']]],
    ]);
    ?>

    <section class="sk-section">
        <div class="sk-wrap sk-wrap--narrow">
            <article class="sk-prose reveal">
                <?php if (trim((string) get_post_field('post_content', get_the_ID())) !== '') : ?>
                    <?php the_content(); ?>
                <?php else : ?>
                    <div class="sk-callout">
                        <strong><?php esc_html_e('Placeholder content', 'site-kit'); ?></strong>
                        <p><?php esc_html_e('Have this text reviewed before going live. The notes below describe what this website actually does, which is usually the hardest part to get right.', 'site-kit'); ?></p>
                    </div>

                    <h2><?php esc_html_e('Controller', 'site-kit'); ?></h2>
                    <p>
                        <?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?><br>
                        <?php echo esc_html($contact['street']); ?><br>
                        <?php echo esc_html($contact['city']); ?><br>
                        <?php esc_html_e('E-mail:', 'site-kit'); ?> <a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    </p>

                    <h2><?php esc_html_e('Hosting and server logs', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('The hosting provider processes technically necessary data (IP address, time, requested URL, browser) in server logs to operate the site securely. The legal basis is our legitimate interest in a functioning, secure website.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Contact form', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('When you use the contact form, the details you enter are sent to us by e-mail so we can answer your request. They are not stored in this website\'s database. The legal basis is your request itself.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Cookies and tracking', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('This website sets no tracking or marketing cookies and loads no external analytics. Logged-in editors receive the usual WordPress session cookies.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('External content', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Fonts are served from this server — no font CDN is contacted. A map is only embedded if one is configured, and then it loads lazily from OpenStreetMap, which receives your IP address when the map loads.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Your rights', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('You have the right to information, correction, deletion, restriction of processing, data portability and objection. Contact us at the address above. You may also complain to a data protection authority.', 'site-kit'); ?></p>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
