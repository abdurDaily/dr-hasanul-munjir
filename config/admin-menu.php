<?php

/*
| Admin sidebar. Items the user lacks permission for are hidden — the routes enforce
| the same permissions server-side. 'active' is a route-name pattern.
*/

return [
    ['label' => 'Overview', 'items' => [
        ['Dashboard', 'bi-grid-1x2-fill', 'admin.dashboard', 'admin.dashboard', null],
    ]],
    ['label' => 'Clinic', 'items' => [
        ['Appointments', 'bi-calendar2-check', 'admin.appointments.index', 'admin.appointments.*', 'appointments.manage', 'pending'],
        ['Availability', 'bi-clock-history', 'admin.schedule.index', 'admin.schedule.*', 'schedule.manage'],
        ['Messages', 'bi-chat-dots', 'admin.messages.index', 'admin.messages.*', 'messages.manage', 'unread'],
        ['Subscribers', 'bi-envelope-paper', 'admin.subscribers.index', 'admin.subscribers.*', 'subscribers.manage'],
    ]],
    ['label' => 'Website', 'items' => [
        ['Dentist Profile', 'bi-person-badge', 'admin.profile.edit', 'admin.profile.*', 'content.manage'],
        ['Treatments', 'bi-heart-pulse', 'admin.treatments.index', 'admin.treatments.*', 'treatments.manage'],
        ['Credentials', 'bi-mortarboard', 'admin.credentials.index', 'admin.credentials.*', 'content.manage'],
        ['Why Choose Me', 'bi-stars', 'admin.highlights.index', 'admin.highlights.*', 'content.manage'],
        ['Smile Gallery', 'bi-emoji-smile', 'admin.smile-cases.index', 'admin.smile-cases.*', 'gallery.manage'],
        ['Clinic Gallery', 'bi-images', 'admin.clinic-photos.index', 'admin.clinic-photos.*', 'gallery.manage'],
        ['Testimonials', 'bi-star', 'admin.testimonials.index', 'admin.testimonials.*', 'testimonials.manage'],
        ['Blog Posts', 'bi-journal-richtext', 'admin.posts.index', 'admin.posts.*', 'blog.manage'],
        ['FAQs', 'bi-question-circle', 'admin.faqs.index', 'admin.faqs.*', 'content.manage'],
        ['Homepage Text', 'bi-layout-text-window', ['admin.settings.edit', 'home'], 'admin.settings.edit:home', 'content.manage'],
    ]],
    ['label' => 'System', 'items' => [
        ['Website Settings', 'bi-gear', ['admin.settings.edit', 'general'], 'admin.settings.edit:general,contact,legal', 'settings.manage'],
        ['SEO', 'bi-search', ['admin.settings.edit', 'seo'], 'admin.settings.edit:seo', 'seo.manage'],
        ['Users', 'bi-people', 'admin.users.index', 'admin.users.*', 'users.manage'],
        ['Roles & Permissions', 'bi-shield-lock', 'admin.roles.index', 'admin.roles.*', 'roles.manage'],
    ]],
];
