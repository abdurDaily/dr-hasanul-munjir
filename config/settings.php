<?php

/*
|--------------------------------------------------------------------------
| Website settings schema
|--------------------------------------------------------------------------
| Single source of truth for every editable site setting: the admin form,
| validation and seeded defaults are all generated from this file.
|
| Text fields marked `emphasis` support *asterisks* to render the italic accent
| font, e.g. "Complete care for *every smile.*"
| type: text | textarea | lines (one item per line) | number | url | email | image | html
*/

return [
    'general' => [
        'label' => 'General',
        'permission' => 'settings.manage',
        'fields' => [
            'clinic_name' => ['label' => 'Clinic name', 'type' => 'text', 'rules' => 'required|max:120', 'default' => 'Munjir Dental Care'],
            'footer_intro' => ['label' => 'Footer introduction', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'Gentle, modern dentistry in Bahaddarhat, Chattogram. Healthy smile, brighter tomorrow.'],
            'footer_cta_title' => ['label' => 'Footer call-to-action heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Ready for a healthier, *happier smile?*'],
            'newsletter_text' => ['label' => 'Newsletter blurb', 'type' => 'text', 'rules' => 'nullable|max:160', 'default' => 'Monthly oral-health tips. No spam, ever.'],
            'notify_emails' => ['label' => 'Booking alert emails (comma separated)', 'type' => 'text', 'rules' => 'nullable|max:300', 'default' => ''],
        ],
    ],

    'contact' => [
        'label' => 'Contact & Social',
        'permission' => 'settings.manage',
        'fields' => [
            'address_line1' => ['label' => 'Address line 1', 'type' => 'text', 'rules' => 'required|max:150', 'default' => 'Beside IFIC Bank (2nd Floor),'],
            'address_line2' => ['label' => 'Address line 2', 'type' => 'text', 'rules' => 'nullable|max:150', 'default' => 'Barai Para Mor, Bahaddarhat, Chattogram'],
            'address_short' => ['label' => 'Short address', 'type' => 'text', 'rules' => 'nullable|max:100', 'default' => 'Barai Para Mor, Bahaddarhat'],
            'address_locality' => ['label' => 'City', 'type' => 'text', 'rules' => 'nullable|max:60', 'default' => 'Chattogram'],
            'phone_primary' => ['label' => 'Primary phone', 'type' => 'text', 'rules' => 'required|max:20', 'default' => '01690-106079'],
            'phone_secondary' => ['label' => 'Secondary phone', 'type' => 'text', 'rules' => 'nullable|max:20', 'default' => '01992-761901'],
            'email' => ['label' => 'Public email', 'type' => 'email', 'rules' => 'nullable|email|max:120', 'default' => ''],
            'whatsapp_number' => ['label' => 'WhatsApp number (international, digits only)', 'type' => 'text', 'rules' => 'nullable|regex:/^[0-9]{8,15}$/', 'default' => '8801690106079'],
            'map_query' => ['label' => 'Google Maps search (embed & directions)', 'type' => 'text', 'rules' => 'nullable|max:200', 'default' => 'IFIC Bank Bahaddarhat, Chattogram'],
            'google_review_url' => ['label' => '"Write a review" link', 'type' => 'url', 'rules' => 'nullable|url|max:300', 'default' => 'https://www.google.com/maps/search/?api=1&query=Munjir+Dental+Care+Bahaddarhat+Chattogram'],
            'facebook_url' => ['label' => 'Facebook URL', 'type' => 'url', 'rules' => 'nullable|url|max:300', 'default' => ''],
            'instagram_url' => ['label' => 'Instagram URL', 'type' => 'url', 'rules' => 'nullable|url|max:300', 'default' => ''],
            'youtube_url' => ['label' => 'YouTube URL', 'type' => 'url', 'rules' => 'nullable|url|max:300', 'default' => ''],
        ],
    ],

    'home' => [
        'label' => 'Homepage',
        'permission' => 'content.manage',
        'fields' => [
            'hero_tag' => ['label' => 'Hero tag', 'type' => 'text', 'rules' => 'nullable|max:40', 'default' => 'Happy Smiles'],
            'hero_title_line1' => ['label' => 'Hero title — line 1', 'type' => 'text', 'emphasis' => true, 'rules' => 'required|max:40', 'default' => "Your Smile's"],
            'hero_title_line2' => ['label' => 'Hero title — line 2', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:40', 'default' => 'Trusted *Care*'],
            'hero_note' => ['label' => 'Hero note', 'type' => 'textarea', 'rules' => 'nullable|max:200', 'default' => "Whether it's a routine visit or a complex procedure, Dr. Munjir ensures your smile stays bright and healthy."],
            'hero_patients' => ['label' => 'Hero patients figure', 'type' => 'text', 'rules' => 'nullable|max:10', 'default' => '5k+'],
            'hero_badge_title' => ['label' => 'Hero badge title', 'type' => 'text', 'rules' => 'nullable|max:30', 'default' => '10+ Years'],
            'hero_badge_text' => ['label' => 'Hero badge text', 'type' => 'text', 'rules' => 'nullable|max:40', 'default' => 'Trusted Dental Care'],
            'marquee_items' => ['label' => 'Scrolling marquee items', 'type' => 'lines', 'rules' => 'nullable|max:600', 'default' => "Root Canal Treatment\nDental Implants\nTeeth Whitening\nOrthodontics\nSmile Designing\nPediatric Care"],
            'stat_years' => ['label' => 'Stat: years experience', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'default' => '10'],
            'stat_patients' => ['label' => 'Stat: patients treated', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'default' => '5000'],
            'stat_procedures' => ['label' => 'Stat: procedures done', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'default' => '8500'],
            'stat_certifications' => ['label' => 'Stat: certifications', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'default' => '12'],
            'about_title' => ['label' => 'About heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Gentle hands, honest advice, *lasting smiles.*'],
            'treatments_title' => ['label' => 'Treatments heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Complete care for *every smile.*'],
            'treatments_aside' => ['label' => 'Treatments intro', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'From routine check-ups to full smile makeovers — modern treatments delivered with precision and genuine care.'],
            'why_title' => ['label' => 'Why-choose-me heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Care that feels *personal* — because it is.'],
            'why_badge' => ['label' => 'Why-choose-me badge', 'type' => 'text', 'rules' => 'nullable|max:40', 'default' => 'Comfort-first dentistry'],
            'experience_title' => ['label' => 'Experience heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'A journey of *learning & care.*'],
            'gallery_title' => ['label' => 'Smile gallery heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Drag to see the *difference.*'],
            'clinic_title' => ['label' => 'Clinic heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'A calm space built *around you.*'],
            'clinic_intro' => ['label' => 'Clinic introduction', 'type' => 'textarea', 'rules' => 'nullable|max:400', 'default' => 'Munjir Dental Care is designed for comfort and strict infection control — bright treatment rooms, modern dental units, digital imaging and a relaxed waiting area.'],
            'clinic_amenities' => ['label' => 'Clinic amenities', 'type' => 'lines', 'rules' => 'nullable|max:600', 'default' => "Autoclave sterilisation\nDigital X-ray & intraoral camera\nRotary endodontic system\nAir-conditioned waiting lounge"],
            'testimonials_title' => ['label' => 'Testimonials heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Kind words from *our patients.*'],
            'google_rating' => ['label' => 'Google rating (leave empty to hide)', 'type' => 'text', 'rules' => 'nullable|numeric|min:1|max:5', 'default' => ''],
            'google_review_count' => ['label' => 'Google review count', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'default' => ''],
            'booking_title' => ['label' => 'Booking heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Select an *appointment time.*'],
            'blog_title' => ['label' => 'Blog heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Advice for a *healthier smile.*'],
            'faq_title' => ['label' => 'FAQ heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => 'Questions, *answered.*'],
            'faq_intro' => ['label' => 'FAQ intro', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => "Can't find what you're looking for? Message us on WhatsApp — we usually reply within the hour."],
            'contact_title' => ['label' => 'Contact heading', 'type' => 'text', 'emphasis' => true, 'rules' => 'nullable|max:120', 'default' => "Let's talk about *your smile.*"],
        ],
    ],

    'booking' => [
        'label' => 'Booking rules',
        'permission' => 'schedule.manage',
        'fields' => [
            'slot_minutes' => ['label' => 'Appointment length (minutes)', 'type' => 'number', 'rules' => 'required|integer|min:10|max:240', 'default' => '30'],
            'booking_window_days' => ['label' => 'How many days ahead patients can book', 'type' => 'number', 'rules' => 'required|integer|min:1|max:365', 'default' => '60'],
            'min_lead_minutes' => ['label' => 'Minimum notice for same-day bookings (minutes)', 'type' => 'number', 'rules' => 'required|integer|min:0|max:1440', 'default' => '30'],
            'booking_policies' => ['label' => 'Appointment policies ("Title: text", one per line)', 'type' => 'lines', 'rules' => 'nullable|max:1000', 'default' => "Rescheduling: Please call at least 3 hours ahead if you need to change your time.\nPreparation: Bring previous X-rays or prescriptions, and arrive 10 minutes early."],
        ],
    ],

    'seo' => [
        'label' => 'SEO',
        'permission' => 'seo.manage',
        'fields' => [
            'seo_title' => ['label' => 'Homepage title', 'type' => 'text', 'rules' => 'required|max:70', 'default' => 'Dr. Hasanul Munjir | Dentist in Chattogram'],
            'seo_description' => ['label' => 'Homepage meta description', 'type' => 'textarea', 'rules' => 'required|max:320', 'default' => 'Gentle, modern dental care by Dr. Hasanul Munjir, BDS (CU), PGT (OMS) — root canals, implants & smile design in Bahaddarhat, Chattogram.'],
            'og_title' => ['label' => 'Social share title', 'type' => 'text', 'rules' => 'nullable|max:90', 'default' => "Dr. Hasanul Munjir — Your Smile's Trusted Care"],
            'og_description' => ['label' => 'Social share description', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'Gentle, modern dentistry in Bahaddarhat, Chattogram — root canals, implants & smile design. Book your visit at Munjir Dental Care.'],
            'og_image' => ['label' => 'Social share image (1200×630)', 'type' => 'image', 'rules' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096|dimensions:min_width=600', 'default' => ''],
            'google_site_verification' => ['label' => 'Google Search Console verification code', 'type' => 'text', 'rules' => 'nullable|max:100', 'default' => ''],
        ],
    ],

    'legal' => [
        'label' => 'Legal pages',
        'permission' => 'settings.manage',
        'fields' => [
            'privacy_policy' => ['label' => 'Privacy policy', 'type' => 'html', 'rules' => 'nullable|max:60000', 'default' => ''],
            'terms_of_use' => ['label' => 'Terms of use', 'type' => 'html', 'rules' => 'nullable|max:60000', 'default' => ''],
        ],
    ],
];
