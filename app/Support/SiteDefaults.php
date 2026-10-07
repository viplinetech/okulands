<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/**
 * Fallback content shown on the public site until the admin fills in the
 * matching field. Every value here is overridable from the admin end.
 *
 * Default photography is a set of free-licence (Pexels) photos of Nigeria that ships
 * with the site in public/images/ng (see CREDITS.txt there). They are stand-ins for
 * real Oku Lands photography, which the admin uploads.
 */
class SiteDefaults
{
    /** URL of a bundled Nigeria photo (self-hosted, so pages never wait on a third party). */
    public static function ng(string $id): string
    {
        return asset("images/ng/{$id}.webp");
    }

    /** The same photo as a stored, host-independent path (used when seeding database rows). */
    public static function ngPath(string $id): string
    {
        return "/images/ng/{$id}.webp";
    }

    public static function heroImages(): array
    {
        // Until the admin uploads hero photos, the slideshow shows branded placeholders.
        return [Placeholder::url('Home · Hero photo')];
    }

    public static function heroHeadline(): string
    {
        return 'Homes built on trust.';
    }

    public static function heroSubheadline(): string
    {
        return 'Bulk land, general contracting and block production in Awka and Enugu, backed by a trusted network of realtors.';
    }

    public static function aboutHeadline(): string
    {
        return 'We make owning land in Nigeria feel safe, transparent and within reach, backed by our own building and block production.';
    }

    public static function aboutBody(): string
    {
        return "Oku Lands & Properties is a multi-service company delivering secure, verified real estate solutions across Nigeria.\n\nBeyond selling land, we stand behind what we sell: every property goes through legal and documentation checks before it is listed, and our own construction and agriculture divisions help clients turn land into homes, farms and lasting value.\n\nOur realtor network extends that trust further, giving ambitious people a fair, transparent way to earn while helping others secure their future.";
    }

    public static function mission(): string
    {
        return 'To make secure, verified land and property ownership accessible to every Nigerian family and investor, through honest dealing and clear documentation.';
    }

    public static function vision(): string
    {
        return 'To be the most trusted name in Nigerian real estate, construction and agriculture, known for integrity in every transaction.';
    }

    public static function values(): array
    {
        return [
            ['title' => 'Integrity', 'desc' => 'We say what we mean and document what we promise.'],
            ['title' => 'Due diligence', 'desc' => 'Titles are verified before a property is ever listed.'],
            ['title' => 'Excellence', 'desc' => 'From first viewing to final handover, the details matter.'],
            ['title' => 'Customer-centricity', 'desc' => 'Every decision starts with the client’s long-term interest.'],
            ['title' => 'Technology-driven', 'desc' => 'Modern tools keep purchases, payments and referrals transparent.'],
            ['title' => 'Sustainability', 'desc' => 'We build and invest with the next generation in mind.'],
        ];
    }

    public static function commitments(): array
    {
        return [
            ['n' => '01', 'title' => 'Verified titles', 'desc' => 'Every property is legally verified before it reaches our listings.'],
            ['n' => '02', 'title' => 'Prime locations', 'desc' => 'Land in areas with real, measurable growth potential.'],
            ['n' => '03', 'title' => 'Transparent purchase', 'desc' => 'Clear documentation and honest communication, always.'],
            ['n' => '04', 'title' => 'Flexible payment plans', 'desc' => 'Instalment options available on select properties.'],
        ];
    }

    /** PLACEHOLDER FIGURES: replace with verified numbers in the admin before launch. */
    public static function stats(): array
    {
        return [
            ['value' => 1101, 'suffix' => '+', 'label' => 'Happy clients'],
            ['value' => 459, 'suffix' => '+', 'label' => 'Properties sold'],
            ['value' => 3, 'suffix' => '', 'label' => 'Services, one company'],
        ];
    }

    public static function faqs(): array
    {
        return [
            ['q' => 'Are your land titles verified?', 'a' => 'Yes. Every property listed goes through legal verification and documentation checks before it appears on our platform.'],
            ['q' => 'Can I pay for land in instalments?', 'a' => 'Flexible payment plans are available on select properties. Contact our team to discuss options for the property you are interested in.'],
            ['q' => 'How do I book a site inspection?', 'a' => 'Send us an enquiry from the Contact page or any property page. Our team will confirm a date and take you to the site.'],
            ['q' => 'Do you offer construction services on purchased land?', 'a' => 'Yes, our construction division can handle your build from foundation to finishing once you own the land.'],
            ['q' => 'How do I join the Oku Lands realtor programme?', 'a' => 'Register as a realtor on our platform, get your unique referral link instantly, and start earning commission on every successful referral, no fees to join.'],
            ['q' => 'Can I be a realtor and a client at the same time?', 'a' => 'Absolutely. Many of our realtors started as clients and now earn commission by referring others while continuing to invest themselves.'],
        ];
    }

    public static function pageBanner(string $page): string
    {
        $sections = [
            'properties' => 'Properties · Banner',
            'about' => 'About us · Banner',
            'services' => 'Services · Banner',
            'gallery' => 'Gallery · Banner',
            'contact' => 'Contact · Banner',
            'blog' => 'Blog · Banner',
        ];

        return Placeholder::url($sections[$page] ?? 'Page · Banner');
    }

    public static function aboutImage(): string
    {
        return Placeholder::url('Home & About · Photo 1');
    }

    public static function aboutImage2(): string
    {
        return Placeholder::url('Home & About · Photo 2');
    }

    /** Shown on the Gallery page only until the admin uploads real project photos. */
    public static function gallerySamples(): array
    {
        $g = fn (string $id, string $title, string $category) => [
            'image' => Placeholder::url($category.' · '.$title), 'title' => $title, 'caption' => null, 'category' => $category,
        ];

        return [
            $g('36622014', 'Townhouse estate, Lekki', 'Real Estate'),
            $g('38513265', 'Development site, Lagos', 'Construction'),
            $g('32956482', 'Green farmland', 'Agriculture'),
            $g('34432716', 'Bungalow estate, Abuja', 'Real Estate'),
            $g('38277835', 'High-rise towers under construction', 'Construction'),
            $g('32860685', 'Cabbage farm', 'Agriculture'),
            $g('27938900', 'Planned residential layout', 'Real Estate'),
            $g('36622005', 'Apartment complex nearing completion', 'Construction'),
            $g('32847478', 'Open farmland', 'Agriculture'),
        ];
    }
}
