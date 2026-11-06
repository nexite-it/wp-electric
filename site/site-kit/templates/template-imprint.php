<?php
/**
 * Template: Impressum.
 *
 * Placeholder imprint for a German craft business (GmbH) built from
 * config.php → brand, contact and legal. Includes the trade-specific details
 * an electrician needs (§ 5 DDG, DL-InfoV): professional title, chamber and
 * the Handwerksordnung as professional regulation.
 *
 * Have the final text checked before launch. Text written in the page editor
 * replaces this placeholder completely.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

if (have_posts()) {
    the_post();
}

$intro   = Content\page_intro('imprint');
$contact = Config\contact();
$legal   = array_map('strval', (array) Config\get('legal', []));
$legal  += array_fill_keys(['managing_director', 'register', 'vat_id', 'profession', 'chamber', 'content_owner'], '');
$company = (string) Config\get('brand.legal_name', Config\brand_name());

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => get_the_title() ?: $intro['title'],
        'crumbs'  => [['label' => get_the_title() ?: $intro['title']]],
    ]);
    ?>

    <section class="sk-section">
        <div class="sk-wrap sk-wrap--narrow">
            <article class="sk-prose">
                <?php if (trim((string) get_post_field('post_content', get_the_ID())) !== '') : ?>
                    <?php the_content(); ?>
                <?php else : ?>
                    <div class="sk-callout">
                        <strong><?php esc_html_e('Platzhalter', 'site-kit'); ?></strong>
                        <p><?php esc_html_e('Alle Angaben stammen aus config.php und sind Beispielwerte. Bitte vor dem Livegang vervollständigen und rechtlich prüfen lassen.', 'site-kit'); ?></p>
                    </div>

                    <h2><?php esc_html_e('Angaben gemäß § 5 DDG', 'site-kit'); ?></h2>
                    <p>
                        <strong><?php echo esc_html($company); ?></strong><br>
                        <?php echo esc_html($contact['street']); ?><br>
                        <?php echo esc_html($contact['city']); ?><br>
                        <?php echo esc_html($contact['country']); ?>
                    </p>

                    <?php if ($legal['managing_director'] !== '') : ?>
                        <h2><?php esc_html_e('Vertreten durch', 'site-kit'); ?></h2>
                        <p><?php echo esc_html(sprintf(__('Geschäftsführer: %s', 'site-kit'), $legal['managing_director'])); ?></p>
                    <?php endif; ?>

                    <h2><?php esc_html_e('Kontakt', 'site-kit'); ?></h2>
                    <p>
                        <?php if ($contact['phone'] !== '') : ?>
                            <?php esc_html_e('Telefon:', 'site-kit'); ?> <a href="<?php echo esc_url(Util\tel($contact['phone']), ['tel']); ?>"><?php echo esc_html($contact['phone']); ?></a><br>
                        <?php endif; ?>
                        <?php esc_html_e('E-Mail:', 'site-kit'); ?> <a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    </p>

                    <?php if ($legal['register'] !== '') : ?>
                        <h2><?php esc_html_e('Registereintrag', 'site-kit'); ?></h2>
                        <p><?php echo esc_html(sprintf(__('Eintragung im Handelsregister: %s', 'site-kit'), $legal['register'])); ?></p>
                    <?php endif; ?>

                    <?php if ($legal['vat_id'] !== '') : ?>
                        <h2><?php esc_html_e('Umsatzsteuer-ID', 'site-kit'); ?></h2>
                        <p><?php echo esc_html(sprintf(__('Umsatzsteuer-Identifikationsnummer gemäß § 27a UStG: %s', 'site-kit'), $legal['vat_id'])); ?></p>
                    <?php endif; ?>

                    <?php if ($legal['profession'] !== '' || $legal['chamber'] !== '') : ?>
                        <h2><?php esc_html_e('Berufsrechtliche Angaben', 'site-kit'); ?></h2>
                        <p>
                            <?php if ($legal['profession'] !== '') : ?>
                                <?php echo esc_html(sprintf(__('Berufsbezeichnung: %s', 'site-kit'), $legal['profession'])); ?><br>
                            <?php endif; ?>
                            <?php if ($legal['chamber'] !== '') : ?>
                                <?php echo esc_html(sprintf(__('Zuständige Kammer: %s', 'site-kit'), $legal['chamber'])); ?><br>
                                <?php esc_html_e('Der Betrieb ist in die Handwerksrolle eingetragen.', 'site-kit'); ?><br>
                            <?php endif; ?>
                            <?php esc_html_e('Berufsrechtliche Regelung: Gesetz zur Ordnung des Handwerks (Handwerksordnung, HwO), abrufbar unter', 'site-kit'); ?>
                            <a href="https://www.gesetze-im-internet.de/hwo/" rel="noopener">gesetze-im-internet.de/hwo</a>
                        </p>
                    <?php endif; ?>

                    <?php if ($legal['content_owner'] !== '') : ?>
                        <h2><?php esc_html_e('Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV', 'site-kit'); ?></h2>
                        <p><?php echo esc_html($legal['content_owner'] . ', ' . trim($contact['street'] . ', ' . $contact['city'], ', ')); ?></p>
                    <?php endif; ?>

                    <h2><?php esc_html_e('Verbraucherstreitbeilegung', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Haftung für Links', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Diese Website enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber verantwortlich.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Urheberrecht', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Die Inhalte und Werke auf dieser Website unterliegen dem deutschen Urheberrecht. Vervielfältigung, Bearbeitung und Verbreitung außerhalb der Grenzen des Urheberrechts bedürfen der schriftlichen Zustimmung.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('Bildnachweise', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Eigene Aufnahmen. Partner- und Herstellerlogos mit freundlicher Genehmigung der jeweiligen Rechteinhaber.', 'site-kit'); ?></p>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
