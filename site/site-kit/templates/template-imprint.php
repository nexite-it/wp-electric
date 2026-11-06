<?php
/**
 * Template: imprint.
 *
 * Placeholder legal page — fill in the details required in your jurisdiction
 * (in Germany: § 5 DDG, plus the responsible person for content).
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

$intro   = Content\page_intro('imprint');
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
                        <p><?php esc_html_e('Replace this with your own imprint text. The block below shows the values from config.php so you can see what is wired up.', 'site-kit'); ?></p>
                    </div>

                    <h2><?php esc_html_e('Information according to § 5 DDG', 'site-kit'); ?></h2>
                    <p>
                        <strong><?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?></strong><br>
                        <?php echo esc_html($contact['street']); ?><br>
                        <?php echo esc_html($contact['city']); ?><br>
                        <?php echo esc_html($contact['country']); ?>
                    </p>

                    <h2><?php esc_html_e('Represented by', 'site-kit'); ?></h2>
                    <p>
                        <?php
                        printf(
                            /* translators: %s: comma separated list of names. */
                            esc_html__('The board: %s', 'site-kit'),
                            esc_html(implode(', ', array_map(
                                static fn(array $person): string => (string) $person['name'],
                                array_filter(Content\people(), static fn(array $person): bool => ($person['group'] ?? '') === 'board')
                            )))
                        );
                        ?>
                    </p>

                    <h2><?php esc_html_e('Contact', 'site-kit'); ?></h2>
                    <p>
                        <?php esc_html_e('Phone:', 'site-kit'); ?> <?php echo esc_html($contact['phone']); ?><br>
                        <?php esc_html_e('E-mail:', 'site-kit'); ?> <a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    </p>

                    <h2><?php esc_html_e('Responsible for content', 'site-kit'); ?></h2>
                    <p><?php echo esc_html((string) Config\get('brand.legal_name', Config\brand_name())); ?>, <?php echo esc_html(trim($contact['street'] . ', ' . $contact['city'], ', ')); ?></p>

                    <h2><?php esc_html_e('Liability for external links', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('This site contains links to external websites. We have no influence over their content and therefore cannot accept liability for it. The respective operator is always responsible for the content of linked pages.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Copyright', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Content published here is subject to copyright. Reproduction beyond private use requires written permission.', 'site-kit'); ?></p>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
