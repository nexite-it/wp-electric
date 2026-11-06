<?php
/**
 * Page copy for Elektro Wendt (placeholder content in German).
 *
 * Business data — phone numbers, hours, address, partner brands — lives in
 * config.php. This file holds the texts the templates render: hero, service
 * descriptions, process, FAQ, values and open positions. Edit the strings
 * here; the layout picks up added or removed entries automatically.
 *
 * Service keys (wallbox, photovoltaik, …) double as anchors on the
 * Leistungen page (/leistungen/#wallbox) and as preselected topics in the
 * contact form (/kontakt/?leistung=wallbox).
 */

namespace SiteKit\Content;

defined('ABSPATH') || exit;

use SiteKit\Config;
use SiteKit\Util;

/** Date offset from today, as Y-m-d. */
function day(int $offset): string
{
    return (string) wp_date('Y-m-d', strtotime(($offset >= 0 ? '+' : '') . $offset . ' days', current_time('timestamp')));
}

/* -------------------------------------------------------------------------
 * Startseite
 * ---------------------------------------------------------------------- */

/** Hero copy. The last title line is set in the accent colour. */
function hero(): array
{
    return [
        'kicker' => __('Elektro-Meisterbetrieb · Bonn & Rhein-Sieg-Kreis', 'site-kit'),
        'title'  => [
            __('Ihr Elektromeister', 'site-kit'),
            __('in Bonn.', 'site-kit'),
        ],
        'lead'   => __('Wallbox, Photovoltaik, Smart Home oder die komplette Elektrik im Altbau: Wir planen, installieren und prüfen nach VDE – mit festem Ansprechpartner, Festpreis-Angebot und sauberer Dokumentation.', 'site-kit'),
        'checks' => [
            sprintf(__('Meisterbetrieb seit %d', 'site-kit'), (int) Config\get('brand.founded', 0)),
            __('Festpreis-Angebot in 48 h', 'site-kit'),
            __('Beim Netzbetreiber eingetragen', 'site-kit'),
        ],
        'primary' => __('Projekt anfragen', 'site-kit'),
    ];
}

/** Qualifications shown in the trust bar. */
function certifications(): array
{
    return [
        ['icon' => 'award',     'title' => __('Meisterbetrieb', 'site-kit'),      'text' => __('Handwerksrolle der HWK Köln', 'site-kit')],
        ['icon' => 'shield',    'title' => __('Innungsfachbetrieb', 'site-kit'),  'text' => __('Elektro- und Informationstechnik', 'site-kit')],
        ['icon' => 'plug',      'title' => __('Installateurverzeichnis', 'site-kit'), 'text' => __('Beim Netzbetreiber eingetragen', 'site-kit')],
        ['icon' => 'clipboard', 'title' => __('Geprüft nach VDE', 'site-kit'),    'text' => __('Prüfprotokoll zu jeder Anlage', 'site-kit')],
    ];
}

/** "Warum wir" block on the start page. */
function why(): array
{
    return [
        'eyebrow' => __('Warum Elektro Wendt', 'site-kit'),
        'title'   => __('Meisterqualität aus Bonn.', 'site-kit'),
        'mark'    => __('Ohne Überraschungen.', 'site-kit'),
        'text'    => __('Wir sind ein inhabergeführter Meisterbetrieb mit 14 Fachkräften – groß genug für komplette Sanierungen, klein genug, dass Sie Ihren Ansprechpartner persönlich kennen. Jede Anlage, die wir übergeben, ist nach VDE geprüft und dokumentiert.', 'site-kit'),
        'points'  => [
            __('Festpreis-Angebot statt Kostenüberraschung', 'site-kit'),
            __('Ein fester Ansprechpartner vom Angebot bis zur Abnahme', 'site-kit'),
            __('Saubere Baustelle: Staubschutz, Schuhüberzieher, Endreinigung', 'site-kit'),
        ],
    ];
}

/**
 * Key figures. `num` is counted up on scroll; `suffix` is printed after it.
 *
 * @return array<int, array{num: int, suffix: string, label: string}>
 */
function stats(): array
{
    return [
        ['num' => Util\years_since((int) Config\get('brand.founded', 0)), 'suffix' => '',  'label' => __('Jahre Erfahrung im Rheinland', 'site-kit')],
        ['num' => 1800, 'suffix' => '+', 'label' => __('abgeschlossene Projekte', 'site-kit')],
        ['num' => 14,   'suffix' => '',  'label' => __('Fachkräfte, davon 3 Meister', 'site-kit')],
        ['num' => 48,   'suffix' => ' h', 'label' => __('bis zum Festpreis-Angebot', 'site-kit')],
    ];
}

/* -------------------------------------------------------------------------
 * Leistungen
 * ---------------------------------------------------------------------- */

/**
 * All services, in display order.
 *
 * home     'feature' = large dark card on the start page, 'card' = regular
 *          card, false = only on the Leistungen page
 * focus    highlighted on the Leistungen page (high-demand services)
 * image    path below assets/img/ — replace the placeholder with a photo
 * hint     placeholder for the message field when this topic is selected
 *
 * @return array<string, array<string, mixed>>
 */
function services(): array
{
    return [
        'wallbox' => [
            'icon'     => 'ev',
            'title'    => __('E-Mobilität & Wallboxen', 'site-kit'),
            'label'    => __('Wallbox', 'site-kit'),
            'home'     => 'feature',
            'focus'    => true,
            'teaser'   => __('Laden Sie Ihr E-Auto sicher und schnell zu Hause – inklusive Anmeldung beim Netzbetreiber und Einbindung in Ihre PV-Anlage.', 'site-kit'),
            'chips'    => [__('11 & 22 kW', 'site-kit'), __('PV-Überschussladen', 'site-kit'), __('Anmeldung inklusive', 'site-kit')],
            'kicker'   => __('E-Mobilität', 'site-kit'),
            'headline' => __('Wallbox-Installation: Ihr E-Auto lädt zu Hause.', 'site-kit'),
            'lead'     => __('Eine Wallbox lädt um ein Vielfaches schneller als die Haushaltssteckdose – und ist für die Dauerlast ausgelegt. Wir prüfen Ihren Hausanschluss, empfehlen das passende Modell und übernehmen die komplette Installation.', 'site-kit'),
            'points'   => [
                __('Vor-Ort-Check von Hausanschluss und Zählerschrank', 'site-kit'),
                __('Herstellerunabhängige Beratung: 11 kW oder 22 kW', 'site-kit'),
                __('Absicherung mit passendem FI-Schutz inkl. DC-Fehlerstromerkennung', 'site-kit'),
                __('Anmeldung (bis 11 kW) bzw. Genehmigung (über 11 kW) beim Netzbetreiber', 'site-kit'),
                __('Einbindung in PV-Anlage und dynamisches Lastmanagement', 'site-kit'),
                __('Inbetriebnahme, Einweisung und Prüfprotokoll', 'site-kit'),
            ],
            'specs'    => [
                __('Ladeleistung', 'site-kit') => __('11 kW / 22 kW', 'site-kit'),
                __('Montageort', 'site-kit')   => __('Garage, Carport, Außenwand, Tiefgarage', 'site-kit'),
                __('Dauer', 'site-kit')        => __('meist ein Arbeitstag', 'site-kit'),
                __('Für', 'site-kit')          => __('Eigenheim, WEG, Gewerbe', 'site-kit'),
            ],
            'cta'      => __('Wallbox-Angebot anfragen', 'site-kit'),
            'image'    => 'site/service-wallbox.svg',
            'hint'     => __('z. B. Fahrzeugmodell, Garage oder Stellplatz, ungefähre Entfernung zum Zählerschrank, Baujahr des Hauses', 'site-kit'),
        ],
        'photovoltaik' => [
            'icon'     => 'solar',
            'title'    => __('PV-Anschluss & Speicher', 'site-kit'),
            'label'    => __('Photovoltaik', 'site-kit'),
            'home'     => false,
            'focus'    => true,
            'teaser'   => __('Wir schließen Ihre Solaranlage normgerecht an – vom Wechselrichter über den Speicher bis zum neuen Zählerschrank.', 'site-kit'),
            'chips'    => [__('Speicher', 'site-kit'), __('Zählerschrank', 'site-kit')],
            'kicker'   => __('Photovoltaik', 'site-kit'),
            'headline' => __('PV-Anlagen anschließen: sicher, normgerecht, angemeldet.', 'site-kit'),
            'lead'     => __('Ihre Solaranlage ist nur so gut wie ihr Anschluss. Wir übernehmen den elektrischen Teil – vom Wechselrichter über den Batteriespeicher bis zum normgerechten Zählerschrank – und stimmen uns direkt mit Ihrem Solarteur und dem Netzbetreiber ab.', 'site-kit'),
            'points'   => [
                __('Anschluss von Wechselrichter und Batteriespeicher', 'site-kit'),
                __('Zählerschrank-Erneuerung nach VDE-AR-N 4100', 'site-kit'),
                __('Überspannungsschutz und Potenzialausgleich', 'site-kit'),
                __('Anmeldung beim Netzbetreiber, Hilfe beim Marktstammdatenregister', 'site-kit'),
                __('Steckersolargeräte (Balkonkraftwerke) prüfen und anschließen', 'site-kit'),
                __('Energiemanagement mit Wallbox und Wärmepumpe', 'site-kit'),
            ],
            'specs'    => [
                __('Anlagen', 'site-kit')       => __('Einfamilienhaus bis Gewerbe', 'site-kit'),
                __('Speicher', 'site-kit')      => __('AC- und DC-gekoppelt', 'site-kit'),
                __('Zählerschrank', 'site-kit') => __('nach VDE-AR-N 4100', 'site-kit'),
                __('Abstimmung', 'site-kit')    => __('mit Solarteur und Netzbetreiber', 'site-kit'),
            ],
            'cta'      => __('PV-Anschluss anfragen', 'site-kit'),
            'image'    => 'site/service-photovoltaik.svg',
            'hint'     => __('z. B. geplante Anlagengröße (kWp), Speicher ja/nein, Alter des Zählerschranks, vorhandenes Angebot eines Solarteurs', 'site-kit'),
        ],
        'smart-home' => [
            'icon'     => 'smart-home',
            'title'    => __('Smart Home', 'site-kit'),
            'label'    => __('Smart Home', 'site-kit'),
            'home'     => 'feature',
            'focus'    => false,
            'teaser'   => __('Licht, Heizung, Rollläden und Sicherheit intelligent vernetzt – per KNX im Neubau oder per Funk zum Nachrüsten.', 'site-kit'),
            'chips'    => [__('KNX & Funk', 'site-kit'), __('Ohne Cloud-Zwang', 'site-kit')],
            'kicker'   => __('Gebäudeautomation', 'site-kit'),
            'headline' => __('Smart Home: Komfort, der mitdenkt.', 'site-kit'),
            'lead'     => __('Ob Neubau mit KNX-Bussystem oder Nachrüstung per Funk im Bestand: Wir planen Ihr Smart Home so, dass es zuverlässig funktioniert – auch ohne Internetverbindung und auch in zehn Jahren noch.', 'site-kit'),
            'points'   => [
                __('KNX-Planung, Programmierung und Inbetriebnahme', 'site-kit'),
                __('Funk-Systeme zur Nachrüstung ohne Stemmarbeiten', 'site-kit'),
                __('Licht-, Rollladen- und Heizungssteuerung', 'site-kit'),
                __('Türkommunikation, Alarm und Anwesenheitssimulation', 'site-kit'),
                __('Energiemonitoring mit PV-Anlage und Wallbox', 'site-kit'),
                __('Bedienung per Taster, Panel, App oder Sprache', 'site-kit'),
            ],
            'specs'    => [
                __('Systeme', 'site-kit')   => __('KNX, Funk-Systeme führender Hersteller', 'site-kit'),
                __('Bedienung', 'site-kit') => __('Taster, Touchpanel, App', 'site-kit'),
                __('Einsatz', 'site-kit')   => __('Neubau und Bestand', 'site-kit'),
            ],
            'cta'      => __('Smart-Home-Beratung anfragen', 'site-kit'),
            'image'    => 'site/service-smart-home.svg',
            'hint'     => __('z. B. Neubau oder Bestand, welche Räume, was gesteuert werden soll (Licht, Rollläden, Heizung …)', 'site-kit'),
        ],
        'altbau' => [
            'icon'     => 'building',
            'title'    => __('Altbausanierung', 'site-kit'),
            'label'    => __('Altbau', 'site-kit'),
            'home'     => 'card',
            'focus'    => false,
            'teaser'   => __('Neue Elektrik für alte Mauern: Wir ersetzen veraltete Leitungen und Sicherungskästen – staubarm und auf Wunsch in Etappen.', 'site-kit'),
            'chips'    => [],
            'kicker'   => __('Sanierung', 'site-kit'),
            'headline' => __('Altbausanierung: neue Elektrik für alte Mauern.', 'site-kit'),
            'lead'     => __('In vielen Bonner Altbauten stecken noch Leitungen aus den 1960er-Jahren – ohne Schutzleiter, ohne FI-Schutzschalter, mit Schraubsicherungen. Wir bringen Ihre Elektrik auf den Stand der Technik, abgestimmt auf Bausubstanz, Denkmalschutz und Ihren Zeitplan.', 'site-kit'),
            'points'   => [
                __('Bestandsaufnahme und E-Check der Altanlage', 'site-kit'),
                __('Erneuerung von Leitungen, Steckdosen und Unterverteilungen', 'site-kit'),
                __('Neuer Zählerschrank nach aktueller Norm', 'site-kit'),
                __('Nachrüstung von FI-/LS-Schutzschaltern und Überspannungsschutz', 'site-kit'),
                __('Staubarme Schlitz- und Bohrarbeiten, Abstimmung mit anderen Gewerken', 'site-kit'),
                __('Netzwerk- und Multimediaverkabelung gleich mit planen', 'site-kit'),
            ],
            'specs'    => [
                __('Gebäude', 'site-kit')      => __('Gründerzeit, Nachkriegsbau, Denkmal', 'site-kit'),
                __('Ausführung', 'site-kit')   => __('bewohnt oder leerstehend', 'site-kit'),
                __('Koordination', 'site-kit') => __('mit Trockenbau, Maler, Sanitär', 'site-kit'),
            ],
            'cta'      => __('Sanierung besprechen', 'site-kit'),
            'image'    => 'site/service-altbau.svg',
            'hint'     => __('z. B. Baujahr, Wohnfläche, Komplettsanierung oder einzelne Räume, gewünschter Zeitraum', 'site-kit'),
        ],
        'kundendienst' => [
            'icon'     => 'wrench',
            'title'    => __('Kundendienst & Reparatur', 'site-kit'),
            'label'    => __('Kundendienst', 'site-kit'),
            'home'     => 'card',
            'focus'    => false,
            'teaser'   => __('Sicherung fliegt, Steckdose defekt, Licht flackert? Unser Kundendienst findet den Fehler – meist schon beim ersten Termin.', 'site-kit'),
            'chips'    => [],
            'kicker'   => __('Service', 'site-kit'),
            'headline' => __('Kundendienst: schnell da, sauber repariert.', 'site-kit'),
            'lead'     => __('Kleine Ursache, großer Ärger: Wenn der FI-Schutzschalter immer wieder auslöst oder eine Steckdose warm wird, sollten Sie nicht warten. Unsere Servicetechniker suchen den Fehler systematisch – mit voll ausgestattetem Servicewagen.', 'site-kit'),
            'points'   => [
                __('Fehlersuche und Reparatur', 'site-kit'),
                __('Austausch von Steckdosen, Schaltern und Leuchten', 'site-kit'),
                __('E-Check und DGUV-V3-Prüfung für Vermieter und Gewerbe', 'site-kit'),
                __('Rauchwarnmelder: Montage und jährliche Wartung', 'site-kit'),
                __('Herd-, Durchlauferhitzer- und Geräteanschluss', 'site-kit'),
                __('24/7-Notdienst bei Stromausfall und Gefahr', 'site-kit'),
            ],
            'specs'    => [
                __('Termin', 'site-kit')     => __('meist innerhalb von 2–3 Werktagen', 'site-kit'),
                __('Notdienst', 'site-kit')  => __('rund um die Uhr', 'site-kit'),
                __('Abrechnung', 'site-kit') => __('transparent nach Aufwand', 'site-kit'),
            ],
            'cta'      => __('Termin anfragen', 'site-kit'),
            'image'    => 'site/service-kundendienst.svg',
            'hint'     => __('z. B. was genau nicht funktioniert, seit wann, ob eine Sicherung auslöst', 'site-kit'),
        ],
    ];
}

/**
 * Topics offered in the contact form: every service plus two extras.
 *
 * @return array<string, array{label: string, icon: string, hint: string}>
 */
function form_topics(): array
{
    $topics = [];
    foreach (services() as $key => $service) {
        $topics[$key] = ['label' => $service['label'], 'icon' => $service['icon'], 'hint' => $service['hint']];
    }

    $topics['bewerbung'] = [
        'label' => __('Bewerbung', 'site-kit'),
        'icon'  => 'helmet',
        'hint'  => __('z. B. gewünschte Stelle, Ausbildung/Erfahrung, frühester Starttermin. Lebenslauf gern als Antwort auf unsere Rückmeldung.', 'site-kit'),
    ];
    $topics['sonstiges'] = [
        'label' => __('Sonstiges', 'site-kit'),
        'icon'  => 'zap',
        'hint'  => __('Beschreiben Sie kurz Ihr Anliegen.', 'site-kit'),
    ];

    return $topics;
}

/** The four steps from request to handover (Leistungen page). */
function process(): array
{
    return [
        ['title' => __('Anfrage', 'site-kit'),           'text' => __('Per Formular, Telefon oder WhatsApp. Ein paar Eckdaten genügen.', 'site-kit')],
        ['title' => __('Vor-Ort-Termin', 'site-kit'),    'text' => __('Wir sehen uns Hausanschluss, Zählerschrank und Leitungswege an – unverbindlich.', 'site-kit')],
        ['title' => __('Festpreis-Angebot', 'site-kit'), 'text' => __('Innerhalb von 48 Stunden, mit nachvollziehbaren Positionen.', 'site-kit')],
        ['title' => __('Umsetzung & Abnahme', 'site-kit'), 'text' => __('Fachgerechte Installation, VDE-Prüfung und Prüfprotokoll für Ihre Unterlagen.', 'site-kit')],
    ];
}

/** FAQ on the Leistungen page. */
function faq(): array
{
    return [
        [
            'q' => __('Was kostet eine Wallbox inklusive Installation?', 'site-kit'),
            'a' => __('Das hängt vor allem vom Weg zwischen Zählerschrank und Stellplatz ab. Für eine Standard-Installation in der Garage liegen die Gesamtkosten meist zwischen 1.500 und 2.500 Euro inklusive Wallbox. Nach dem Vor-Ort-Termin erhalten Sie einen verbindlichen Festpreis.', 'site-kit'),
        ],
        [
            'q' => __('Muss ich meine Wallbox anmelden?', 'site-kit'),
            'a' => __('Ja. Ladeeinrichtungen bis 11 kW müssen beim Netzbetreiber angemeldet werden, Ladepunkte mit mehr als 11 kW sind genehmigungspflichtig. Beides übernehmen wir für Sie.', 'site-kit'),
        ],
        [
            'q' => __('Brauche ich für meine PV-Anlage einen neuen Zählerschrank?', 'site-kit'),
            'a' => __('Häufig ja: Ältere Zählerschränke erfüllen die Anforderungen der VDE-AR-N 4100 oft nicht, etwa beim Platz für moderne Messeinrichtungen und Überspannungsschutz. Wir prüfen das beim Vor-Ort-Termin und sagen Ihnen ehrlich, ob ein Austausch nötig ist.', 'site-kit'),
        ],
        [
            'q' => __('Wie schnell sind Sie im Notfall vor Ort?', 'site-kit'),
            'a' => __('Unser Notdienst ist rund um die Uhr erreichbar. Im Bonner Stadtgebiet sind wir in der Regel innerhalb von 60 Minuten bei Ihnen, im Rhein-Sieg-Kreis je nach Entfernung etwas später. Den Notdienstzuschlag nennen wir Ihnen vorab am Telefon.', 'site-kit'),
        ],
        [
            'q' => __('Arbeiten Sie auch für Hausverwaltungen und Gewerbe?', 'site-kit'),
            'a' => __('Ja. Für Hausverwaltungen, Eigentümergemeinschaften und Gewerbekunden übernehmen wir Wartung, E-Check und DGUV-V3-Prüfungen sowie Ladeinfrastruktur in Tiefgaragen – auf Wunsch mit Rahmenvertrag.', 'site-kit'),
        ],
    ];
}

/* -------------------------------------------------------------------------
 * Über uns
 * ---------------------------------------------------------------------- */

/** Intro used when the "Über uns" page has no editor content. */
function about(): array
{
    return [
        'eyebrow'   => __('Meisterbetrieb seit 1998', 'site-kit'),
        'title'     => __('Fest verwurzelt in Bonn und im Rhein-Sieg-Kreis.', 'site-kit'),
        'paragraphs' => [
            __('Was 1998 als Zwei-Mann-Betrieb in Bonn-Beuel begann, ist heute ein Team aus 14 Fachkräften: drei Elektrotechnikermeister, Gesellinnen und Gesellen, Servicetechniker und Auszubildende. Geblieben ist der Anspruch, jede Installation so auszuführen, als wäre es unsere eigene.', 'site-kit'),
            __('Wir arbeiten in Altbauwohnungen in der Südstadt ebenso wie in Neubauten in Sankt Augustin, für Familien, Hausverwaltungen und Gewerbebetriebe. Viele Aufträge kommen über Empfehlungen aus der Nachbarschaft – darauf sind wir stolz, und daran arbeiten wir jeden Tag.', 'site-kit'),
        ],
        'signature' => __('Markus Wendt', 'site-kit'),
        'role'      => __('Elektrotechnikermeister und Inhaber', 'site-kit'),
        'badge'     => __('Meisterbetrieb in Bonn', 'site-kit'),
        'photo_alt' => __('Das Team von Elektro Wendt vor dem Firmengebäude in Bonn', 'site-kit'),
    ];
}

/** Three values on the "Über uns" page. */
function values(): array
{
    return [
        [
            'icon'  => 'clock',
            'title' => __('Pünktlichkeit', 'site-kit'),
            'text'  => __('Wir kommen, wann wir es sagen. Und wenn sich etwas verschiebt, erfahren Sie es von uns – nicht erst, wenn niemand vor der Tür steht.', 'site-kit'),
        ],
        [
            'icon'  => 'eye',
            'title' => __('Transparenz', 'site-kit'),
            'text'  => __('Festpreis-Angebote mit nachvollziehbaren Positionen. Mehraufwand besprechen wir vorher mit Ihnen – nicht erst auf der Rechnung.', 'site-kit'),
        ],
        [
            'icon'  => 'shield',
            'title' => __('Sicherheit', 'site-kit'),
            'text'  => __('Jede Anlage wird nach VDE geprüft und dokumentiert. Sie erhalten ein Prüfprotokoll – für Ihre Versicherung und Ihr gutes Gefühl.', 'site-kit'),
        ],
    ];
}

/** Open positions and benefits (Karriere). */
function jobs(): array
{
    return [
        'eyebrow'   => __('Karriere', 'site-kit'),
        'title'     => __('Wir suchen Verstärkung.', 'site-kit'),
        'text'      => __('Moderne Technik, feste Teams, pünktlicher Feierabend: Bei uns arbeiten Sie an Projekten, die Spaß machen – von der Wallbox bis zum KNX-Smart-Home.', 'site-kit'),
        'positions' => [
            ['title' => __('Elektroniker/in für Energie- und Gebäudetechnik (m/w/d)', 'site-kit'), 'meta' => __('Vollzeit · ab sofort', 'site-kit')],
            ['title' => __('Servicetechniker/in Kundendienst (m/w/d)', 'site-kit'), 'meta' => __('Vollzeit · ab sofort', 'site-kit')],
            ['title' => __('Ausbildung Elektroniker/in (m/w/d)', 'site-kit'), 'meta' => __('Start 1. August', 'site-kit')],
        ],
        'benefits'  => [
            __('Übertarifliche Bezahlung und 30 Tage Urlaub', 'site-kit'),
            __('Moderne Werkzeuge, Messtechnik und Servicefahrzeuge', 'site-kit'),
            __('Wir fördern Ihre Weiterbildung bis zum Meister', 'site-kit'),
            __('Feste Teams, kurze Wege, keine Leiharbeit', 'site-kit'),
        ],
        'apply'     => __('Jetzt bewerben', 'site-kit'),
        'quick'     => __('Kurzbewerbung per WhatsApp', 'site-kit'),
        'quick_text' => __('Hallo Elektro Wendt, ich interessiere mich für eine Stelle bei Ihnen:', 'site-kit'),
    ];
}

/* -------------------------------------------------------------------------
 * Page intros and SEO
 * ---------------------------------------------------------------------- */

/**
 * Intro copy and SEO fields per template.
 *
 * Keys are template names (services, about, contact …), so slugs can change
 * in config.php without touching this file.
 *
 * @return array{eyebrow: string, title: string, lead: string, seo_title?: string, seo_desc?: string}
 */
function page_intro(string $key): array
{
    $map = [
        'start' => [
            'eyebrow'   => '',
            'title'     => __('Startseite', 'site-kit'),
            'lead'      => '',
            'seo_title' => __('Elektriker in Bonn für Wallbox, PV & Smart Home', 'site-kit'),
            'seo_desc'  => (string) Config\get('brand.description', ''),
        ],
        'services' => [
            'eyebrow'   => __('Leistungen', 'site-kit'),
            'title'     => __('Elektrotechnik für Zuhause und Gewerbe.', 'site-kit'),
            'lead'      => __('Von der Wallbox über den PV-Anschluss bis zur kompletten Altbausanierung: Hier sehen Sie, was wir für Sie tun – und wie wir dabei vorgehen.', 'site-kit'),
            'seo_title' => __('Leistungen: Wallbox, PV & Smart Home in Bonn', 'site-kit'),
            'seo_desc'  => __('Wallbox-Installation, PV-Anschluss mit Speicher, KNX-Smart-Home, Altbausanierung und Kundendienst – vom Elektro-Meisterbetrieb in Bonn und im Rhein-Sieg-Kreis.', 'site-kit'),
        ],
        'about' => [
            'eyebrow'   => __('Über uns', 'site-kit'),
            'title'     => __('Ein Meisterbetrieb. Aus Bonn. Für die Region.', 'site-kit'),
            'lead'      => __('Seit 1998 sorgen wir in Bonn und im Rhein-Sieg-Kreis für sichere Elektrik – mit einem Team, das Sie persönlich kennen.', 'site-kit'),
            'seo_title' => __('Über uns: Elektro-Meisterbetrieb in Bonn', 'site-kit'),
            'seo_desc'  => __('Inhabergeführter Elektro-Meisterbetrieb mit 14 Fachkräften in Bonn. Pünktlich, transparent, sicher – und auf der Suche nach Verstärkung.', 'site-kit'),
        ],
        'contact' => [
            'eyebrow'   => __('Kontakt', 'site-kit'),
            'title'     => __('Erzählen Sie uns von Ihrem Projekt.', 'site-kit'),
            'lead'      => __('Beschreiben Sie kurz Ihr Vorhaben – wir melden uns werktags innerhalb von 24 Stunden. Im Notfall rufen Sie bitte direkt unseren Notdienst an.', 'site-kit'),
            'seo_title' => __('Kontakt & Projektanfrage', 'site-kit'),
            'seo_desc'  => __('Projekt anfragen, Rückruf vereinbaren oder den 24/7-Notdienst erreichen: Elektro-Meisterbetrieb für Bonn und den Rhein-Sieg-Kreis.', 'site-kit'),
        ],
        'imprint' => [
            'eyebrow' => __('Rechtliches', 'site-kit'),
            'title'   => __('Impressum', 'site-kit'),
            'lead'    => '',
        ],
        'privacy' => [
            'eyebrow' => __('Rechtliches', 'site-kit'),
            'title'   => __('Datenschutzerklärung', 'site-kit'),
            'lead'    => '',
        ],

        // Dormant kit templates (their post types are switched off in config.php).
        'events'    => ['eyebrow' => __('Termine', 'site-kit'), 'title' => __('Termine', 'site-kit'), 'lead' => ''],
        'news'      => ['eyebrow' => __('Aktuelles', 'site-kit'), 'title' => __('Aktuelles', 'site-kit'), 'lead' => ''],
        'documents' => ['eyebrow' => __('Downloads', 'site-kit'), 'title' => __('Dokumente', 'site-kit'), 'lead' => ''],
        'team'      => team_intro(),
    ];

    return $map[$key] ?? ['eyebrow' => '', 'title' => ucfirst($key), 'lead' => ''];
}

/** Team page intro (dormant kit template). */
function team_intro(): array
{
    return [
        'eyebrow' => __('Team', 'site-kit'),
        'title'   => __('Unser Team', 'site-kit'),
        'lead'    => '',
    ];
}

/* -------------------------------------------------------------------------
 * Demo content of the kit's default post types.
 *
 * Those post types are switched off in config.php, so the seeder never asks
 * for these. They stay defined (and empty) so that re-enabling a post type
 * cannot crash the seeder.
 * ---------------------------------------------------------------------- */

function people(): array
{
    return [];
}

function events(): array
{
    return [];
}

function news(): array
{
    return [];
}

function documents(): array
{
    return [];
}
