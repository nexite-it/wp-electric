<?php
/**
 * Template: Datenschutzerklärung.
 *
 * Placeholder privacy policy that describes what this website actually does:
 * local fonts, no tracking, form data by e-mail only, WhatsApp and
 * OpenStreetMap only after the visitor's own click. Have it reviewed before
 * launch. Text written in the page editor replaces it completely.
 */

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Content;
use SiteKit\Util;

if (have_posts()) {
    the_post();
}

$intro   = Content\page_intro('privacy');
$contact = Config\contact();
$company = (string) Config\get('brand.legal_name', Config\brand_name());

\SiteKit\Template\partial('header', []);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => $intro['title'],
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
                        <p><?php esc_html_e('Dieser Text beschreibt, was diese Website technisch tatsächlich tut. Bitte vor dem Livegang rechtlich prüfen lassen und um Angaben zu Hosting-Anbieter und E-Mail-Dienst ergänzen.', 'site-kit'); ?></p>
                    </div>

                    <h2><?php esc_html_e('1. Verantwortlicher', 'site-kit'); ?></h2>
                    <p>
                        <?php echo esc_html($company); ?><br>
                        <?php echo esc_html($contact['street']); ?><br>
                        <?php echo esc_html($contact['city']); ?><br>
                        <?php esc_html_e('E-Mail:', 'site-kit'); ?> <a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    </p>

                    <h2><?php esc_html_e('2. Hosting und Server-Logfiles', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Beim Aufruf dieser Website verarbeitet unser Hosting-Anbieter technisch notwendige Daten (IP-Adresse, Datum und Uhrzeit, aufgerufene Seite, Browsertyp) in Server-Logfiles. Rechtsgrundlage ist unser berechtigtes Interesse an einem sicheren und stabilen Betrieb der Website (Art. 6 Abs. 1 lit. f DSGVO).', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('3. Kontaktformular, E-Mail und Telefon', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Wenn Sie uns über das Formular, per E-Mail oder telefonisch kontaktieren, verarbeiten wir Ihre Angaben (z. B. Name, Kontaktdaten, Projektbeschreibung), um Ihre Anfrage zu beantworten und ein Angebot zu erstellen. Rechtsgrundlage ist die Durchführung vorvertraglicher Maßnahmen (Art. 6 Abs. 1 lit. b DSGVO) sowie Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO).', 'site-kit'); ?></p>
                    <p><?php esc_html_e('Formularangaben werden nicht in der Datenbank dieser Website gespeichert, sondern per E-Mail an uns übermittelt. Wir löschen sie, sobald Ihre Anfrage abschließend bearbeitet ist und keine gesetzlichen Aufbewahrungspflichten (etwa bei einem Auftrag) entgegenstehen.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('4. Bewerbungen', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Bewerbungsunterlagen verarbeiten wir ausschließlich zur Durchführung des Bewerbungsverfahrens (Art. 6 Abs. 1 lit. b DSGVO). Kommt kein Arbeitsverhältnis zustande, löschen wir die Unterlagen spätestens sechs Monate nach Abschluss des Verfahrens.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('5. WhatsApp', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Die WhatsApp-Schaltflächen sind einfache Links. Erst wenn Sie darauf klicken, öffnet sich WhatsApp, ein Dienst der WhatsApp Ireland Ltd. (Meta). Dabei können Daten auch in die USA übertragen werden. Die Nutzung ist freiwillig; alternativ erreichen Sie uns jederzeit per Telefon, E-Mail oder Formular.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('6. Karte (OpenStreetMap)', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Auf der Kontaktseite zeigen wir zunächst eine selbst erstellte Grafik unseres Einsatzgebiets. Die interaktive Karte der OpenStreetMap Foundation wird erst geladen, wenn Sie auf „Interaktive Karte laden“ klicken; dabei wird Ihre IP-Adresse an OpenStreetMap übertragen. Rechtsgrundlage ist Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO).', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('7. Cookies, Tracking und Schriftarten', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Diese Website setzt keine Tracking- oder Marketing-Cookies und verwendet keine Analysedienste. Schriftarten werden von unserem eigenen Server geladen, nicht von Google oder anderen Anbietern.', 'site-kit'); ?></p>

                    <h2><?php esc_html_e('8. Ihre Rechte', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch (Art. 15–21 DSGVO). Eine erteilte Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen. Wenden Sie sich dazu an die oben genannte Adresse.', 'site-kit'); ?></p>
                    <p><?php esc_html_e('Sie haben außerdem das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren, zum Beispiel bei der Landesbeauftragten für Datenschutz und Informationsfreiheit Nordrhein-Westfalen.', 'site-kit'); ?></p>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
