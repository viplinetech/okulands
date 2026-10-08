<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 *
 * Fixed wording of the public website. This is part of the site, not something the admin edits:
 * change it here and the site changes with it. Admins manage photos, contact details and figures instead.
 */

namespace App\Support;

class StaticText
{
    public static function tagline(): string
    {
        return 'Homes Built on Trust';
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
        return 'Oku Lands & Properties is a multi-service company delivering secure, verified real estate solutions across Nigeria.

Beyond selling land, we stand behind what we sell: every property goes through legal and documentation checks before it is listed, and our own construction and agriculture divisions help clients turn land into homes, farms and lasting value.

Our realtor network extends that trust further, giving ambitious people a fair, transparent way to earn while helping others secure their future.';
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
        return array (
  0 => 
  array (
    'desc' => 'We say what we mean and document what we promise.',
    'title' => 'Integrity',
  ),
  1 => 
  array (
    'desc' => 'Titles are verified before a property is ever listed.',
    'title' => 'Due diligence',
  ),
  2 => 
  array (
    'desc' => 'From first viewing to final handover, the details matter.',
    'title' => 'Excellence',
  ),
  3 => 
  array (
    'desc' => 'Every decision starts with the client’s long-term interest.',
    'title' => 'Customer-centricity',
  ),
  4 => 
  array (
    'desc' => 'Modern tools keep purchases, payments and referrals transparent.',
    'title' => 'Technology-driven',
  ),
  5 => 
  array (
    'desc' => 'We build and invest with the next generation in mind.',
    'title' => 'Sustainability',
  ),
);
    }

    public static function commitments(): array
    {
        return array (
  0 => 
  array (
    'n' => '01',
    'desc' => 'Every property is legally verified before it reaches our listings.',
    'title' => 'Verified titles',
  ),
  1 => 
  array (
    'n' => '02',
    'desc' => 'Land in areas with real, measurable growth potential.',
    'title' => 'Prime locations',
  ),
  2 => 
  array (
    'n' => '03',
    'desc' => 'Clear documentation and honest communication, always.',
    'title' => 'Transparent purchase',
  ),
  3 => 
  array (
    'n' => '04',
    'desc' => 'Instalment options available on select properties.',
    'title' => 'Flexible payment plans',
  ),
);
    }

    public static function ceoName(): string
    {
        return 'Mr. Paul Ozoemena (Okunaenwu na Amansea)';
    }

    public static function ceoTitle(): string
    {
        return 'CEO';
    }

    public static function ceoMessage(): string
    {
        return '<p>Dear Esteemed Client,</p><p>Buying land in Nigeria is not a small decision. It is often the fruit of years of hard work and sacrifice, and we know how many people have lost money to fake titles and double sales.</p><p>That is why Oku Lands &amp; Properties exists. Every property we offer is properly verified, fully documented, and open for you to inspect before you pay a kobo. No hidden deals, no pressure, no surprises.</p><p>More than 1,100 families and investors have trusted us, whether they wanted a home, farmland, or an investment that grows every year. Flexible payment plans are available, because owning land should not be for a few people only.</p><p>Land does not lose value, and the best time to buy was yesterday. The next best time is today.</p><p>Book a free inspection, come and see for yourself, and let us help you own your land the right way.</p>';
    }
}
