<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Every fixed writeup on the public website, in one place.
 *
 * Each entry has the wording the site launched with (the default) and is editable in the admin under
 * "Page content". Only edits are stored; anything untouched keeps showing the default, so the site is
 * never blank. Field types:
 *   text      one line            heading  one line, *word* marks the highlighted accent words
 *   textarea  a short paragraph   lines    one item per line     pairs  a list of title + description
 */
class SiteCopy
{
    private static ?array $flat = null;

    /** @return array<string, array{label: string, hint: string, fields: array<string, array>}> */
    public static function groups(): array
    {
        $t = fn (string $label, string $default, string $type = 'text', ?string $hint = null) => compact('label', 'default', 'type', 'hint');

        return [
            'home' => [
                'label' => 'Home page',
                'hint' => 'The home page previews every other page. Photos and stats are in Site settings.',
                'fields' => [
                    'home.about.eyebrow' => $t('About section: small label', 'About Oku Lands'),
                    'home.about.button' => $t('About section: photo button', 'Our story'),
                    'home.ceo.eyebrow' => $t('CEO section: small label', 'A word from our CEO'),
                    'home.ceo.link' => $t('CEO section: link', 'Read the full message'),
                    'home.services.eyebrow' => $t('Services section: small label', 'What we do'),
                    'home.services.title' => $t('Services section: headline', 'Three services. *One trusted company.*', 'heading'),
                    'home.services.link' => $t('Services section: link', 'All services'),
                    'home.featured.eyebrow' => $t('Listings section: small label', 'Featured listings'),
                    'home.featured.title' => $t('Listings section: headline', 'Land worth *owning.*', 'heading'),
                    'home.featured.link' => $t('Listings section: link', 'View all properties'),
                    'home.featured.empty_eyebrow' => $t('No listings yet: small label', 'Private catalogue'),
                    'home.featured.empty_title' => $t('No listings yet: headline', 'New verified listings are released to enquirers first.'),
                    'home.featured.empty_text' => $t('No listings yet: text', 'Tell us what you are looking for and we will send you the current catalogue with title documents.', 'textarea'),
                    'home.featured.empty_button' => $t('No listings yet: button', 'Request the catalogue'),
                    'home.why.eyebrow' => $t('Why us: small label', 'Why Oku Lands'),
                    'home.why.title' => $t('Why us: headline', 'Built on trust. *Backed by process.*', 'heading'),
                    'home.why.text' => $t('Why us: paragraph', 'Four commitments behind every purchase, from the first viewing to the day the land is yours.', 'textarea'),
                    'home.gallery.eyebrow' => $t('Gallery section: small label', 'Gallery'),
                    'home.gallery.title' => $t('Gallery section: headline', 'See our *work.*', 'heading'),
                    'home.gallery.link' => $t('Gallery section: link', 'Open the gallery'),
                    'home.blog.eyebrow' => $t('Blog section: small label', 'From the blog'),
                    'home.blog.title' => $t('Blog section: headline', 'Insights for *smarter buyers.*', 'heading'),
                    'home.blog.link' => $t('Blog section: link', 'All articles'),
                    'home.faq.eyebrow' => $t('FAQ section: small label', 'FAQ'),
                    'home.faq.title' => $t('FAQ section: headline', 'Questions, *answered.*', 'heading'),
                    'home.faq.link' => $t('FAQ section: link', 'Browse the Help Center'),
                    'home.contact.eyebrow' => $t('Enquiry section: small label', 'Get in touch'),
                    'home.contact.title' => $t('Enquiry section: headline', 'Let’s find your *perfect plot.*', 'heading'),
                    'shared.stories' => $t('Client reviews: small label (Home and About)', 'Client stories'),
                ],
            ],

            'realtor' => [
                'label' => 'Realtor programme',
                'hint' => 'The dark "Earn with Oku Lands" panel on the home page.',
                'fields' => [
                    'home.realtor.eyebrow' => $t('Small label', 'Realtor Management System'),
                    'home.realtor.title' => $t('Headline', 'Earn with *Oku Lands.*', 'heading'),
                    'home.realtor.text' => $t('Paragraph', 'Become a realtor and earn commission on every sale you refer, plus a share from everyone you bring into the network.', 'textarea'),
                    'home.realtor.button1' => $t('Main button', 'Become a realtor'),
                    'home.realtor.button2' => $t('Second button', 'Realtor login'),
                    'home.realtor.cards' => $t('Benefit cards', json_encode([
                        ['Personal referral link', 'Your own unique link, ready the moment you register.'],
                        ['Real-time dashboard', 'Track clicks, leads and conversions as they happen.'],
                        ['Downline commissions', 'Earn a share from realtors you bring into the network.'],
                        ['Transparent payouts', 'Know exactly what you have earned and when it is paid.'],
                    ]), 'pairs', 'Four cards work best.'),
                ],
            ],

            'about' => [
                'label' => 'About page',
                'hint' => 'Story text, mission, vision, values, team and CEO message are in Site settings.',
                'fields' => [
                    'about.hero.eyebrow' => $t('Banner: small label', 'About us'),
                    'about.hero.title' => $t('Banner: headline', 'Rooted in'),
                    'about.hero.accent' => $t('Banner: highlighted words', 'trust.'),
                    'about.hero.subtitle' => $t('Banner: sub-text', 'A multi-service company delivering secure, verified real estate, construction and agriculture solutions across Nigeria.', 'textarea'),
                    'about.story.eyebrow' => $t('Story: small label', 'Our story'),
                    'about.story.button1' => $t('Story: first button', 'Our services'),
                    'about.story.button2' => $t('Story: second button', 'Talk to us'),
                    'about.ceo.eyebrow' => $t('CEO message: small label', 'Message from our CEO'),
                    'about.mission.label' => $t('Mission card label', 'Our mission'),
                    'about.vision.label' => $t('Vision card label', 'Our vision'),
                    'about.values.eyebrow' => $t('Values: small label', 'What guides us'),
                    'about.values.title' => $t('Values: headline', 'Principles we *don’t bend.*', 'heading'),
                    'about.team.eyebrow' => $t('Team: small label', 'Leadership'),
                    'about.team.title' => $t('Team: headline', 'The people *behind it.*', 'heading'),
                ],
            ],

            'services' => [
                'label' => 'Services page',
                'hint' => 'Each service itself (title, photo, description) is under Services in the menu.',
                'fields' => [
                    'services.hero.eyebrow' => $t('Banner: small label', 'Services'),
                    'services.hero.title' => $t('Banner: headline', 'Everything you need,'),
                    'services.hero.accent' => $t('Banner: highlighted words', 'under one roof.'),
                    'services.hero.subtitle' => $t('Banner: sub-text', 'From verified land to finished buildings and productive farmland, one accountable team stands behind every step.', 'textarea'),
                    'services.empty_title' => $t('No services yet: headline', 'Services coming soon.'),
                    'services.empty_text' => $t('No services yet: text', 'We are updating this page. In the meantime, speak with our team directly.', 'textarea'),
                    'services.process.eyebrow' => $t('Process: small label', 'How it works'),
                    'services.process.title' => $t('Process: headline', 'From enquiry to *ownership.*', 'heading'),
                    'services.process.steps' => $t('Process: steps', json_encode([
                        ['Enquire', 'Tell us what you are looking for: location, budget and purpose.'],
                        ['Inspect', 'We take you to the site so you see the land before you decide.'],
                        ['Verify', 'Documentation and title checks are explained to you in plain terms.'],
                        ['Own', 'Complete payment, receive your documents and, if you wish, start building.'],
                    ]), 'pairs', 'Four steps work best.'),
                    'services.cta.title' => $t('Closing panel: headline', 'Let’s talk about your plans.'),
                    'services.cta.button1' => $t('Closing panel: first button', 'Contact Oku Lands'),
                    'services.cta.button2' => $t('Closing panel: second button', 'Book a free inspection'),
                    'services.cta.text' => $t('Closing panel: text', 'Tell us which service you need and our team will reach out.', 'textarea'),
                ],
            ],

            'properties' => [
                'label' => 'Properties page',
                'hint' => 'The listings themselves are under Properties in the menu.',
                'fields' => [
                    'properties.hero.eyebrow' => $t('Banner: small label', 'Properties'),
                    'properties.hero.title' => $t('Banner: headline', 'Find your'),
                    'properties.hero.accent' => $t('Banner: highlighted words', 'next plot.'),
                    'properties.hero.subtitle' => $t('Banner: sub-text', 'Verified land and property across real estate, construction and agriculture. Every listing is documented before it goes live.', 'textarea'),
                    'properties.none_title' => $t('No listings yet: headline', 'Listings are on their way.'),
                    'properties.none_text' => $t('No listings yet: text', 'New verified properties are released to enquirers first. Tell us what you are looking for and we will send you the catalogue.', 'textarea'),
                    'properties.nomatch_title' => $t('Search found nothing: headline', 'Nothing matches just yet.'),
                    'properties.nomatch_text' => $t('Search found nothing: text', 'Try widening your search, or tell us what you need and we will look for it.', 'textarea'),
                    'properties.cta.title' => $t('Closing panel: headline', 'Can’t find the right plot?'),
                    'property.card.button' => $t('Listing card: button', 'Book an inspection'),
                    'property.card.button_sold' => $t('Listing card: button on sold listings', 'Find similar'),
                    'properties.cta.button1' => $t('Closing panel: first button', 'Contact Oku Lands'),
                    'properties.cta.button2' => $t('Closing panel: second button', 'Become a realtor'),
                    'properties.cta.text' => $t('Closing panel: text', 'Our team sources verified land beyond what is listed. Tell us your location and budget.', 'textarea'),
                ],
            ],

            'gallery' => [
                'label' => 'Gallery page',
                'hint' => 'The photos themselves are under Gallery in the menu.',
                'fields' => [
                    'gallery.hero.eyebrow' => $t('Banner: small label', 'Gallery'),
                    'gallery.hero.title' => $t('Banner: headline', 'Our work,'),
                    'gallery.hero.accent' => $t('Banner: highlighted words', 'in pictures.'),
                    'gallery.hero.subtitle' => $t('Banner: sub-text', 'Developments, building sites and farmland, captured across our three services.', 'textarea'),
                    'gallery.empty_title' => $t('No photos yet: headline', 'Photos coming soon.'),
                    'gallery.empty_text' => $t('No photos yet: text', 'We are preparing our project gallery. Meanwhile, browse our current listings.', 'textarea'),
                    'gallery.cta.title' => $t('Closing panel: headline', 'Like what you see?'),
                    'gallery.cta.button1' => $t('Closing panel: first button', 'Book a free inspection'),
                    'gallery.cta.button2' => $t('Closing panel: second button', 'Contact Oku Lands'),
                    'gallery.cta.text' => $t('Closing panel: text', 'Book a free site inspection and see our work in person.', 'textarea'),
                ],
            ],

            'blog' => [
                'label' => 'Blog page',
                'hint' => 'The articles themselves are under Blog in the menu.',
                'fields' => [
                    'blog.hero.eyebrow' => $t('Banner: small label', 'The Oku Lands Blog'),
                    'blog.hero.title' => $t('Banner: headline', 'Insights for'),
                    'blog.hero.accent' => $t('Banner: highlighted words', 'smarter buyers.'),
                    'blog.hero.subtitle' => $t('Banner: sub-text', 'Practical guidance on land, titles, construction and farmland investment in Nigeria.', 'textarea'),
                    'blog.none_title' => $t('No articles yet: headline', 'Articles are on their way.'),
                    'blog.none_text' => $t('No articles yet: text', 'We are preparing guides on buying land safely, verifying titles and investing in farmland. Check back soon.', 'textarea'),
                    'blog.cta.title' => $t('Closing panel: headline', 'Questions about buying land?'),
                    'blog.cta.button1' => $t('Closing panel: first button', 'Contact Oku Lands'),
                    'blog.cta.button2' => $t('Closing panel: second button', 'Browse properties'),
                    'blog.cta.text' => $t('Closing panel: text', 'Our team is happy to walk you through the process, with no obligation.', 'textarea'),
                ],
            ],

            'contact' => [
                'label' => 'Contact page',
                'hint' => 'Phone, email, address and map are in Site settings.',
                'fields' => [
                    'contact.hero.eyebrow' => $t('Banner: small label', 'Contact'),
                    'contact.hero.title' => $t('Banner: headline', 'Let’s find your'),
                    'contact.hero.accent' => $t('Banner: highlighted words', 'perfect plot.'),
                    'contact.hero.subtitle' => $t('Banner: sub-text', 'Book a free inspection, ask about a listing, or talk to us about building and farmland. We reply quickly.', 'textarea'),
                    'contact.form.title' => $t('Form: headline', 'Send us a message'),
                    'contact.form.text' => $t('Form: paragraph', 'Share a few details and the right person will get back to you, usually the same day.', 'textarea'),
                ],
            ],

            'faq' => [
                'label' => 'Help Center page',
                'hint' => 'The questions and answers are under FAQs in the menu.',
                'fields' => [
                    'faq.hero.eyebrow' => $t('Banner: small label', 'Help Center'),
                    'faq.hero.title' => $t('Banner: headline', 'How can we'),
                    'faq.hero.accent' => $t('Banner: highlighted words', 'help you?'),
                    'faq.hero.subtitle' => $t('Banner: sub-text', 'Search our knowledge base for quick answers about buying land, payments, inspections and the realtor programme.', 'textarea'),
                    'faq.empty_title' => $t('No answers yet: headline', 'Answers coming soon.'),
                    'faq.empty_text' => $t('No answers yet: text', 'We are building our knowledge base. Meanwhile, our team is happy to help directly.', 'textarea'),
                    'faq.help.eyebrow' => $t('Bottom section: small label', 'Still need help?'),
                    'faq.help.title' => $t('Bottom section: headline', 'Talk to a *real person.*', 'heading'),
                ],
            ],

            'legal' => [
                'label' => 'Legal pages',
                'hint' => 'The Privacy Policy and Terms of Use pages linked in the footer. The starting wording is general: have your lawyer review it, then paste the final text here.',
                'fields' => [
                    'legal.updated' => $t('"Last updated" date shown on both pages', 'October 2026'),
                    'legal.privacy' => $t('Privacy Policy', LegalDefaults::privacy(), 'rich', 'Use the toolbar for headings, lists and links.'),
                    'legal.terms' => $t('Terms of Use', LegalDefaults::terms(), 'rich', 'Use the toolbar for headings, lists and links.'),
                ],
            ],

            'footer' => [
                'label' => 'Footer & call-to-action',
                'hint' => 'Shown on every page: the closing panel above the footer and the footer itself.',
                'fields' => [
                    'cta.title' => $t('Closing panel: default headline', 'Ready to secure your future?'),
                    'cta.text' => $t('Closing panel: default text', 'Speak with our team today, or become a realtor and earn from every referral you make.', 'textarea'),
                    'cta.button1' => $t('Closing panel: first button', 'Book a free inspection'),
                    'cta.button2' => $t('Closing panel: second button', 'Become a realtor'),
                    'footer.title' => $t('Footer: headline', 'Your land.|*Your legacy.*', 'heading', 'Use | where you want a line break.'),
                ],
            ],
        ];
    }

    /** All fields keyed by their id. */
    public static function fields(): array
    {
        return self::$flat ??= collect(self::groups())->flatMap(fn ($g) => $g['fields'])->all();
    }

    public static function forget(): void
    {
        self::$flat = null;
    }

    /** The launch wording for a field, decoded for list types. */
    public static function default(string $key): string|array
    {
        $field = self::fields()[$key] ?? null;

        return $field ? self::decode($field['type'], $field['default']) : '';
    }

    /** The wording to show right now: the admin's edit if there is one, otherwise the default. */
    public static function value(string $key): string|array
    {
        // Fixed wording: it is part of the website, not something admins edit.
        return self::default($key);
    }

    private static function decode(string $type, string $default): string|array
    {
        return match ($type) {
            'lines' => array_values(array_filter(array_map('trim', preg_split('/\R/', $default)))),
            'pairs' => json_decode($default, true) ?: [],
            default => $default,
        };
    }
}
