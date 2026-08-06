<?php

return [
    'name' => env('FULAWALA_NAME', 'Fulawala'),
    'tagline' => env('FULAWALA_TAGLINE', 'Fresh flowers, sacred moments, beautiful celebrations.'),
    'description' => env('FULAWALA_DESCRIPTION', 'Fulawala delivers fresh flowers, pooja packets, flower subscriptions and event decoration services.'),
    'email' => env('FULAWALA_EMAIL', 'support@fulawala.com'),
    'phone' => env('FULAWALA_PHONE'),
    'whatsapp' => env('FULAWALA_WHATSAPP'),
    'address' => env('FULAWALA_ADDRESS', 'Odisha, India'),
    'business_hours' => env('FULAWALA_BUSINESS_HOURS', 'Monday to Sunday, 6:00 AM – 8:00 PM'),
    'contact_notification_email' => env('FULAWALA_CONTACT_NOTIFICATION_EMAIL', env('FULAWALA_EMAIL', 'support@fulawala.com')),
    'social' => [
        'facebook' => env('FULAWALA_FACEBOOK_URL'),
        'instagram' => env('FULAWALA_INSTAGRAM_URL'),
        'youtube' => env('FULAWALA_YOUTUBE_URL'),
    ],
];
