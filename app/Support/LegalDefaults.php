<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Support;

/**
 * Starting wording for the Privacy Policy and Terms of Use pages. It is general information written for a Nigerian
 * real-estate company; the owner should have a lawyer review it and paste the final text in Admin > Page content > Legal pages.
 */
class LegalDefaults
{
    public static function privacy(): string
    {
        return <<<'HTML'
<p>This Privacy Policy explains how Oku Lands &amp; Properties ("Oku Lands", "we", "us") collects, uses and protects your personal information when you use our website, book an inspection, contact us or join our realtor programme. We handle personal data in line with the Nigeria Data Protection Act 2023.</p>
<h2>Information we collect</h2>
<ul>
<li><strong>Details you give us:</strong> your name, phone number, email address, the property or service you are interested in, and any message you send through our forms.</li>
<li><strong>Realtor accounts:</strong> if you register as a realtor, your account details, phone number and, if you add them, bank details used only to pay your commission.</li>
<li><strong>Referral information:</strong> if you arrive through a realtor's link, a small cookie remembers which realtor referred you so they can be credited.</li>
<li><strong>Basic technical data:</strong> such as your browser type and IP address, used to keep the site secure and working properly.</li>
</ul>
<h2>How we use it</h2>
<ul>
<li>To respond to your enquiry and arrange site inspections.</li>
<li>To prepare and complete property, construction and farmland transactions.</li>
<li>To run our realtor programme, calculate and pay commissions and prevent misuse.</li>
<li>To keep our website and systems secure, and to meet legal obligations.</li>
</ul>
<h2>Who sees your information</h2>
<p>Your contact details are seen only by the Oku Lands team. If you came through a realtor's link, that realtor is told that a booking was made and for which property, but <strong>never your name, phone number or email address</strong>. We do not sell your personal data. We share it only with service providers who help us run the website, or where the law requires it.</p>
<h2>How long we keep it</h2>
<p>We keep enquiry and transaction records for as long as needed to serve you and to meet legal and accounting requirements, then delete or anonymise them.</p>
<h2>Cookies</h2>
<p>We use a small number of essential cookies: to keep you signed in, to remember your light or dark theme, and to credit a referring realtor. We do not use advertising cookies.</p>
<h2>Your rights</h2>
<p>You may ask us to show you the personal data we hold about you, correct it, delete it, or stop using it for a purpose you did not agree to. To do so, contact us using the details on our Contact page and we will respond promptly.</p>
<h2>Security</h2>
<p>We protect your information with access controls, encryption of sensitive details, and secure connections. No system is perfectly secure, but we work hard to keep your data safe.</p>
<h2>Changes to this policy</h2>
<p>We may update this policy from time to time. The date at the top shows when it was last changed.</p>
<h2>Contact us</h2>
<p>For any question about your privacy, please use the contact details shown on our Contact page.</p>
HTML;
    }

    public static function terms(): string
    {
        return <<<'HTML'
<p>These Terms of Use apply to your use of the Oku Lands &amp; Properties website. By using the site you agree to them. If you do not agree, please do not use it.</p>
<h2>About the information on this site</h2>
<ul>
<li>We work to keep listings, prices, sizes and descriptions accurate, but they may change and are not a binding offer.</li>
<li>A property is only reserved or sold once the relevant agreement has been signed and the required payment received.</li>
<li>Photographs and illustrations are for guidance and may not show the exact plot or finished building.</li>
</ul>
<h2>Inspections and enquiries</h2>
<p>Booking an inspection or sending an enquiry does not create an obligation to buy, and does not guarantee that a property remains available. Our team will contact you to confirm details.</p>
<h2>Buying and payment</h2>
<p>All sales are governed by the written agreement between you and Oku Lands. Please read it carefully, and pay only through the official channels and accounts confirmed by our team in writing.</p>
<h2>The realtor programme</h2>
<ul>
<li>Realtors must represent Oku Lands honestly and must not make promises, give guarantees or quote prices on the company's behalf.</li>
<li>Commission is earned and paid according to the programme rules in force when the sale is completed and approved. Rates may change for future sales.</li>
<li>Client contact details are kept by Oku Lands. Realtors must not attempt to obtain them or to deal with a referred client outside the company.</li>
<li>We may suspend or close an account for misuse, misrepresentation or breach of these terms.</li>
</ul>
<h2>Using the website</h2>
<p>You agree not to misuse the site: for example by attempting to break into it, overload it, copy it in bulk, or submit false information. We may block access where we suspect misuse.</p>
<h2>Intellectual property</h2>
<p>The content, design, logo and photographs on this site belong to Oku Lands &amp; Properties or its licensors and may not be copied or reused without our written permission.</p>
<h2>Limits of liability</h2>
<p>To the fullest extent the law allows, Oku Lands is not liable for losses that result from using this website or relying on information on it without confirming it with us. Nothing here limits any right you have under Nigerian law that cannot be excluded.</p>
<h2>Governing law</h2>
<p>These terms are governed by the laws of the Federal Republic of Nigeria.</p>
<h2>Changes and contact</h2>
<p>We may update these terms from time to time; the date at the top shows the latest version. Questions? Please use the contact details on our Contact page.</p>
HTML;
    }
}
