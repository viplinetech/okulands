<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/**
 * Single source of truth for the Realtor app and Admin navigation.
 *
 * Realtor `bar` values place an item in the phone/tablet bottom bar:
 *   'always' = shown at every width, '600' / '840' = appears once the screen is at least that wide.
 * Anything not shown in the bar at the current width is listed in the "More" sheet instead, so
 * nothing is ever unreachable. `fab` marks the raised centre action.
 */
class Nav
{
    /** @return array<int, array{key:string,label:string,route:string,icon:string,match:string,bar:?string,fab?:bool,group:string}> */
    public static function realtor(): array
    {
        return [
            ['key' => 'home', 'label' => 'Home', 'route' => 'realtor.dashboard', 'icon' => 'home', 'match' => 'realtor.dashboard', 'bar' => 'always', 'side' => 'left', 'group' => 'Overview'],
            ['key' => 'leads', 'label' => 'Bookings', 'route' => 'realtor.leads', 'icon' => 'inbox', 'match' => 'realtor.leads*', 'bar' => 'always', 'side' => 'left', 'group' => 'Sales'],
            ['key' => 'team', 'label' => 'Team', 'route' => 'realtor.downline', 'icon' => 'users', 'match' => 'realtor.downline*', 'bar' => '600', 'side' => 'left', 'group' => 'Sales'],
            ['key' => 'alerts', 'label' => 'Alerts', 'route' => 'realtor.notifications', 'icon' => 'bell', 'match' => 'realtor.notifications*', 'bar' => '840', 'side' => 'left', 'group' => 'Account'],
            ['key' => 'share', 'label' => 'Share', 'route' => 'realtor.share', 'icon' => 'send', 'match' => 'realtor.share*', 'bar' => 'always', 'fab' => true, 'group' => 'Grow'],
            ['key' => 'earnings', 'label' => 'Earnings', 'route' => 'realtor.earnings', 'icon' => 'wallet', 'match' => 'realtor.earnings*', 'bar' => 'always', 'side' => 'right', 'group' => 'Sales'],
            ['key' => 'properties', 'label' => 'Listings', 'route' => 'realtor.properties', 'icon' => 'building', 'match' => 'realtor.properties*', 'bar' => '600', 'side' => 'right', 'group' => 'Grow'],
            ['key' => 'profile', 'label' => 'Profile', 'route' => 'realtor.profile', 'icon' => 'user', 'match' => 'realtor.profile*', 'bar' => '840', 'side' => 'right', 'group' => 'Account'],
            ['key' => 'security', 'label' => 'Security', 'route' => 'realtor.security', 'icon' => 'shield', 'match' => 'realtor.security*', 'bar' => null, 'group' => 'Account'],
            ['key' => 'help', 'label' => 'Help', 'route' => 'faq', 'icon' => 'help', 'match' => 'faq', 'bar' => null, 'group' => 'Account', 'external' => true],
        ];
    }

    /** @return array<string, array<int, array{label:string,route:string,icon:string,match:string}>> */
    public static function admin(): array
    {
        return [
            // Grouped by what the admin is trying to do, in the order they usually do it.
            'Today' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid', 'match' => 'admin.dashboard'],
            ],
            'Customers & sales' => [
                ['label' => 'Enquiries & bookings', 'route' => 'admin.leads.index', 'icon' => 'inbox', 'match' => 'admin.leads*'],
                ['label' => 'Sales', 'route' => 'admin.sales.index', 'icon' => 'tag', 'match' => 'admin.sales*'],
                ['label' => 'Realtors', 'route' => 'admin.realtors.index', 'icon' => 'users', 'match' => 'admin.realtors*'],
                ['label' => 'Commissions', 'route' => 'admin.commissions.index', 'icon' => 'wallet', 'match' => 'admin.commissions*'],
                ['label' => 'Withdrawals', 'route' => 'admin.withdrawals.index', 'icon' => 'bank', 'match' => 'admin.withdrawals*'],
                ['label' => 'Reports', 'route' => 'admin.reports', 'icon' => 'chart', 'match' => 'admin.reports*'],
            ],
            'Website' => [
                ['label' => 'Properties', 'route' => 'admin.properties.index', 'icon' => 'building', 'match' => 'admin.properties*'],
                ['label' => 'Services', 'route' => 'admin.services.index', 'icon' => 'key', 'match' => 'admin.services*'],
                ['label' => 'Gallery', 'route' => 'admin.gallery.index', 'icon' => 'image', 'match' => 'admin.gallery*'],
                ['label' => 'Blog articles', 'route' => 'admin.posts.index', 'icon' => 'newspaper', 'match' => 'admin.posts*'],
                ['label' => 'Client reviews', 'route' => 'admin.testimonials.index', 'icon' => 'star', 'match' => 'admin.testimonials*'],
                ['label' => 'Help Center questions', 'route' => 'admin.faqs.index', 'icon' => 'help', 'match' => 'admin.faqs*'],
                ['label' => 'Website settings', 'route' => 'admin.settings', 'icon' => 'sliders', 'match' => 'admin.settings*'],
            ],
            'Account' => [
                ['label' => 'My security', 'route' => 'admin.security', 'icon' => 'shield', 'match' => 'admin.security*'],
                ['label' => 'History', 'route' => 'admin.activity', 'icon' => 'activity', 'match' => 'admin.activity*'],
            ],
        ];
    }
}
