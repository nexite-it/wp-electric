<?php
/**
 * Demo content for fresh installs.
 *
 * Everything here is generic placeholder material: it exists so a new project
 * has a complete, clickable site on first load. Replace it with real content in
 * the WordPress admin, or set features.seed_demo_content = false in config.php
 * to start with empty archives.
 *
 * Dates are relative to install time, so events stay "upcoming" and the demo
 * does not visibly age.
 */

namespace SiteKit\Content;

defined('ABSPATH') || exit;

/** Date offset from today, as Y-m-d. */
function day(int $offset): string
{
    return (string) wp_date('Y-m-d', strtotime(($offset >= 0 ? '+' : '') . $offset . ' days', current_time('timestamp')));
}

/** Home page hero copy (also used when the start template has no content). */
function hero(): array
{
    return [
        'kicker'   => __('A local organization', 'site-kit'),
        'title'    => [
            __('Clear.', 'site-kit'),
            __('Reliable.', 'site-kit'),
            __('For everyone.', 'site-kit'),
        ],
        'lead'     => __('This is a placeholder hero. Replace it with one or two sentences that explain what you do, for whom, and why it matters.', 'site-kit'),
        'primary'  => ['label' => __('Get in touch', 'site-kit'), 'slug' => 'contact'],
        'secondary' => ['label' => __('Learn more', 'site-kit'), 'slug' => 'about'],
    ];
}

/** Numbers band on the start page. */
function stats(): array
{
    return [
        ['num' => 12, 'suffix' => '', 'label' => __('Years of work', 'site-kit')],
        ['num' => 40, 'suffix' => '+', 'label' => __('Active members', 'site-kit')],
        ['num' => 25, 'suffix' => '', 'label' => __('Events per year', 'site-kit')],
        ['num' => 100, 'suffix' => '%', 'label' => __('Volunteer-run', 'site-kit')],
    ];
}

/** Marquee topics on the start page. */
function topics(): array
{
    return [
        __('Community', 'site-kit'),
        __('Events', 'site-kit'),
        __('Volunteering', 'site-kit'),
        __('Education', 'site-kit'),
        __('Networking', 'site-kit'),
        __('Local projects', 'site-kit'),
    ];
}

/** What-we-do principles (start page). */
function principles(): array
{
    return [
        ['title' => __('Open', 'site-kit'), 'text' => __('Meetings, minutes and decisions are public. Anyone can ask questions at any time.', 'site-kit')],
        ['title' => __('Close', 'site-kit'), 'text' => __('We answer e-mails ourselves — no ticket system, no round trips.', 'site-kit')],
        ['title' => __('Connected', 'site-kit'), 'text' => __('We work with partner organizations and bring local topics to a wider audience.', 'site-kit')],
    ];
}

/** Programme / focus areas. */
function pillars(): array
{
    return [
        [
            'icon'   => 'users',
            'title'  => __('Community', 'site-kit'),
            'points' => [
                __('Regular <strong>meet-ups</strong> for members and guests', 'site-kit'),
                __('A <strong>buddy programme</strong> for newcomers', 'site-kit'),
                __('Working groups that anyone can join', 'site-kit'),
            ],
        ],
        [
            'icon'   => 'calendar',
            'title'  => __('Events', 'site-kit'),
            'points' => [
                __('<strong>Workshops</strong> and talks throughout the year', 'site-kit'),
                __('<strong>Social evenings</strong> without an agenda', 'site-kit'),
                __('<strong>Open formats</strong> for partner organizations', 'site-kit'),
            ],
        ],
        [
            'icon'   => 'megaphone',
            'title'  => __('Advocacy', 'site-kit'),
            'points' => [
                __('<strong>Positions</strong> written and published openly', 'site-kit'),
                __('<strong>Talks</strong> with decision makers', 'site-kit'),
                __('<strong>Transparent</strong> documentation of results', 'site-kit'),
            ],
        ],
        [
            'icon'   => 'handshake',
            'title'  => __('Partnerships', 'site-kit'),
            'points' => [
                __('<strong>Joint projects</strong> with local groups', 'site-kit'),
                __('<strong>Shared events</strong> and cross-promotion', 'site-kit'),
                __('A <strong>network</strong> that outlasts single campaigns', 'site-kit'),
            ],
        ],
    ];
}

/** Team page intro. */
function team_intro(): array
{
    return [
        'eyebrow' => __('Who we are', 'site-kit'),
        'title'   => __('The people behind it', 'site-kit'),
        'lead'    => __('Every role here is voluntary. Add your team as “People” in the WordPress admin — the grid fills itself.', 'site-kit'),
    ];
}

/** Team roles for the demo. */
function people(): array
{
    return [
        ['name' => __('Alex Example', 'site-kit'), 'role' => __('Chair', 'site-kit'), 'group' => 'board', 'email' => 'alex@example.com'],
        ['name' => __('Sam Sample', 'site-kit'), 'role' => __('Deputy chair', 'site-kit'), 'group' => 'board', 'email' => 'sam@example.com'],
        ['name' => __('Robin Demo', 'site-kit'), 'role' => __('Treasurer', 'site-kit'), 'group' => 'board', 'email' => 'robin@example.com'],
        ['name' => __('Jamie Placeholder', 'site-kit'), 'role' => __('Secretary', 'site-kit'), 'group' => 'board', 'email' => 'jamie@example.com'],
        ['name' => __('Casey Sample', 'site-kit'), 'role' => __('Events', 'site-kit'), 'group' => 'team'],
        ['name' => __('Morgan Demo', 'site-kit'), 'role' => __('Communications', 'site-kit'), 'group' => 'team'],
        ['name' => __('Taylor Placeholder', 'site-kit'), 'role' => __('Partnerships', 'site-kit'), 'group' => 'team'],
        ['name' => __('Jordan Sample', 'site-kit'), 'role' => __('Volunteering', 'site-kit'), 'group' => 'team'],
    ];
}

/** Demo events (relative dates so they stay upcoming). */
function events(): array
{
    return [
        [
            'title'    => __('Open evening: meet the team', 'site-kit'),
            'date'     => day(12),
            'time'     => '19:00',
            'location' => __('Community hall, Main Street 1', 'site-kit'),
            'type'     => __('Social', 'site-kit'),
            'excerpt'  => __('No agenda, no slides — just an evening to meet members and ask anything you like.', 'site-kit'),
            'body'     => __('Come by, grab a drink and find out what we do. There is no membership requirement and no dress code; bring a friend if you like.', 'site-kit'),
            'registration' => false,
        ],
        [
            'title'    => __('Workshop: getting started with volunteering', 'site-kit'),
            'date'     => day(26),
            'time'     => '18:30',
            'location' => __('Seminar room 2, Library building', 'site-kit'),
            'type'     => __('Workshop', 'site-kit'),
            'excerpt'  => __('A practical session on how projects are planned, funded and handed over.', 'site-kit'),
            'body'     => __('We walk through a real project from idea to hand-over: who decides, how money works, and what usually goes wrong. Bring a project idea if you have one.', 'site-kit'),
            'registration' => true,
        ],
        [
            'title'    => __('Talk: what our city needs next', 'site-kit'),
            'date'     => day(41),
            'time'     => '19:00',
            'location' => __('Town hall, Room 3', 'site-kit'),
            'type'     => __('Talk', 'site-kit'),
            'excerpt'  => __('A conversation with local decision makers about the next two years.', 'site-kit'),
            'body'     => __('We invited representatives from the city council to discuss their plans and take questions from the audience. The discussion will be documented on this site.', 'site-kit'),
            'registration' => true,
        ],
        [
            'title'    => __('Summer get-together', 'site-kit'),
            'date'     => day(-18),
            'time'     => '17:00',
            'location' => __('Riverside park', 'site-kit'),
            'type'     => __('Social', 'site-kit'),
            'excerpt'  => __('Our annual summer evening — thanks to everyone who came by.', 'site-kit'),
            'body'     => __('Around sixty people joined us for food, music and far too many conversations about the next project.', 'site-kit'),
            'registration' => false,
        ],
        [
            'title'    => __('New members welcome evening', 'site-kit'),
            'date'     => day(-52),
            'time'     => '18:00',
            'location' => __('Office, Back Street 7', 'site-kit'),
            'type'     => __('Social', 'site-kit'),
            'excerpt'  => __('An evening for everyone who joined during the last months.', 'site-kit'),
            'body'     => __('A short introduction to how we work, followed by an open Q&A and snacks.', 'site-kit'),
            'registration' => false,
        ],
    ];
}

/** Demo news. */
function news(): array
{
    return [
        [
            'title'   => __('We published our annual report', 'site-kit'),
            'date'    => day(-4),
            'excerpt' => __('A full year in numbers: what we did, what it cost and what we learned.', 'site-kit'),
            'body'    => __('The annual report covers every project, the financial statement and the things that did not work out. It is available in the documents section.', 'site-kit'),
        ],
        [
            'title'   => __('Welcome to our new website', 'site-kit'),
            'date'    => day(-11),
            'excerpt' => __('Faster, clearer and easier to maintain: the site now lives in a Git repository.', 'site-kit'),
            'body'    => __('Every change is versioned, reviewed and deployed automatically. Content stays in WordPress where it belongs, while design and templates are maintained in code.', 'site-kit'),
        ],
        [
            'title'   => __('Two new working groups started', 'site-kit'),
            'date'    => day(-25),
            'excerpt' => __('Transport and education — both open to new members right away.', 'site-kit'),
            'body'    => __('Both groups met for the first time this month and set themselves a schedule for the next six months.', 'site-kit'),
        ],
        [
            'title'   => __('Thanks to everyone who helped at the summer event', 'site-kit'),
            'date'    => day(-40),
            'excerpt' => __('Thirty volunteers, one evening, and a lot of borrowed chairs.', 'site-kit'),
            'body'    => __('Setting up took four hours and clearing up took two. We could not have done it without you.', 'site-kit'),
        ],
    ];
}

/** Demo documents. */
function documents(): array
{
    return [
        ['title' => __('Annual report', 'site-kit'), 'date' => day(-4), 'type' => __('Report', 'site-kit'), 'excerpt' => __('Projects, finances and results of the last year.', 'site-kit')],
        ['title' => __('Statutes', 'site-kit'), 'date' => day(-240), 'type' => __('Official', 'site-kit'), 'excerpt' => __('The current version of our statutes, as adopted by the general meeting.', 'site-kit')],
        ['title' => __('Financial guidelines', 'site-kit'), 'date' => day(-190), 'type' => __('Official', 'site-kit'), 'excerpt' => __('How budgets are planned, approved and documented.', 'site-kit')],
        ['title' => __('Brand assets', 'site-kit'), 'date' => day(-120), 'type' => __('Press', 'site-kit'), 'excerpt' => __('Logos and colours for partners and press.', 'site-kit')],
    ];
}

/** FAQ entries shown on the contact page. */
function faq(): array
{
    return [
        [
            'q' => __('Do I need to be a member to join an event?', 'site-kit'),
            'a' => __('No. Most events are open to everyone; the ones that need registration say so on the event page.', 'site-kit'),
        ],
        [
            'q' => __('How much time does volunteering take?', 'site-kit'),
            'a' => __('As much as you want. Some people help once a year, others are in a working group every week.', 'site-kit'),
        ],
        [
            'q' => __('How can I support you financially?', 'site-kit'),
            'a' => __('Get in touch — we are happy about one-off donations and about long-term support alike.', 'site-kit'),
        ],
    ];
}

/** Short intro copy per demo page. */
function page_intro(string $slug): array
{
    $map = [
        'about' => [
            'eyebrow' => __('About us', 'site-kit'),
            'title'   => __('What we stand for', 'site-kit'),
            'lead'    => __('Two or three sentences about your organization: who you are, what you do and why it matters. This text is editable in the WordPress admin.', 'site-kit'),
        ],
        'events' => [
            'eyebrow' => __('Calendar', 'site-kit'),
            'title'   => __('Events', 'site-kit'),
            'lead'    => __('Everything coming up — and an archive of what already happened.', 'site-kit'),
        ],
        'news' => [
            'eyebrow' => __('Updates', 'site-kit'),
            'title'   => __('News', 'site-kit'),
            'lead'    => __('Short updates, announcements and retrospectives.', 'site-kit'),
        ],
        'documents' => [
            'eyebrow' => __('Downloads', 'site-kit'),
            'title'   => __('Documents', 'site-kit'),
            'lead'    => __('Reports, guidelines and everything else worth keeping on file.', 'site-kit'),
        ],
        'contact' => [
            'eyebrow' => __('Say hello', 'site-kit'),
            'title'   => __('Contact', 'site-kit'),
            'lead'    => __('Questions, ideas or a project you want to talk about? Write us — we answer ourselves.', 'site-kit'),
        ],
        'team' => team_intro(),
        'imprint' => [
            'eyebrow' => __('Legal', 'site-kit'),
            'title'   => __('Imprint', 'site-kit'),
            'lead'    => '',
        ],
        'privacy' => [
            'eyebrow' => __('Legal', 'site-kit'),
            'title'   => __('Privacy policy', 'site-kit'),
            'lead'    => '',
        ],
    ];

    return $map[$slug] ?? ['eyebrow' => '', 'title' => ucfirst($slug), 'lead' => ''];
}
