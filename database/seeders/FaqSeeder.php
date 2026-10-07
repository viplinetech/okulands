<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

/**
 * Starter knowledge base. Every answer is deliberately general (no invented prices,
 * timelines or policies) so it stays true; the admin refines wording and adds specifics.
 * Safe to re-run: existing questions are left untouched.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $kb = [
            'Buying Land' => [
                ['How do I buy land or property from Oku Lands?', "Browse our listings, choose a property, and book a free site inspection. Our team will walk you through the documentation and payment options, and once payment is complete you receive your documents.\n\nYou can start from any property page, or send us a message on the Contact page."],
                ['Are your land titles verified?', 'Yes. Every property listed goes through legal verification and documentation checks before it appears on our platform. Our team explains the documents to you in plain terms before you pay.'],
                ['Can I see the land before I pay?', 'Absolutely. We encourage every buyer to inspect the land in person. Book a free inspection and our team will take you to the site.'],
                ['What documents will I receive?', 'The documents depend on the property. Before you make any payment, our team will tell you exactly which documents come with the property you are buying, so there are no surprises.'],
            ],
            'Payments & Plans' => [
                ['Can I pay for land in instalments?', 'Flexible payment plans are available on select properties. Contact our team to discuss the options for the property you are interested in.'],
                ['How can I make payments safely?', 'Always confirm payment details directly with our office before you pay, and ask for a receipt for every payment. If you are ever unsure, call or message us on WhatsApp using the details on our Contact page.'],
            ],
            'Inspections' => [
                ['How do I book a site inspection?', 'Use the enquiry form on any property page or on the Contact page, or message us on WhatsApp. Our team will confirm a date and take you to the site.'],
                ['Is the site inspection free?', 'Yes, booking a site inspection is free. It is part of how we help you buy with confidence.'],
            ],
            'Construction' => [
                ['Do you offer construction services on purchased land?', 'Yes. Our construction division can handle your build from foundation to finishing once you own the land.'],
                ['Can you design and build to my plan?', 'Our construction division offers architectural design and building delivery. Share your plan or brief with us and we will advise on the best way forward.'],
            ],
            'Agriculture' => [
                ['What agricultural opportunities do you offer?', 'We offer verified farmland and agribusiness opportunities built for long-term, sustainable returns. Current availability appears under Properties, or you can ask our team directly.'],
                ['Do you guide buyers on how to use farmland?', 'Yes. Our agriculture division supports you with guidance on land use and management after acquisition.'],
            ],
            'Realtor Programme' => [
                ['How do I join the Oku Lands realtor programme?', 'Register as a realtor on our platform and you receive your unique referral link instantly. There are no fees to join.'],
                ['How do realtors earn commission?', 'You earn commission on every successful sale that comes through your referral link. Your dashboard shows your clicks, leads, sales and commission so you always know where you stand.'],
                ['What is a downline?', 'If you refer another realtor, they become part of your downline and you earn a share from their sales too. You can see everyone you have brought in on your dashboard.'],
                ['What if someone I referred buys weeks later, or calls the office instead?', 'Every visitor who arrives through your link is tagged to you. Even if they later chat with our team, call the office, or buy weeks afterwards, the sale is credited to the realtor who referred them.'],
                ['Can I be a realtor and a client at the same time?', 'Yes. Many realtors started as clients and now earn commission by referring others while continuing to invest themselves.'],
            ],
            'About Us' => [
                ['Where is Oku Lands located and how can I reach you?', 'Our office is in Awka, Anambra State. The Contact page has our full address, phone number, WhatsApp, email and directions.'],
            ],
        ];

        $order = 1;
        foreach ($kb as $category => $entries) {
            foreach ($entries as [$question, $answer]) {
                FaqItem::firstOrCreate(
                    ['question' => $question],
                    ['category' => $category, 'answer' => $answer, 'sort_order' => $order++]
                );
            }
        }
    }
}
