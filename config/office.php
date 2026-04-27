<?php

$memberName = trim((string) env('MEMBER_NAME', ''));
$nameParts = $memberName === '' ? [] : preg_split('/\s+/', $memberName);

return [
    'member_name' => $memberName,
    'member_first_name' => env('MEMBER_FIRST_NAME', $nameParts[0] ?? ''),
    'member_last_name' => env('MEMBER_LAST_NAME', count($nameParts) > 1 ? end($nameParts) : ''),
    'member_title' => env('MEMBER_TITLE', 'Representative'),
    'member_party' => env('MEMBER_PARTY', ''),
    'member_state' => env('MEMBER_STATE', ''),
    'member_district' => env('MEMBER_DISTRICT', ''),
    'member_bioguide_id' => env('MEMBER_BIOGUIDE_ID', ''),
    'member_photo_url' => env('MEMBER_PHOTO_URL'),
    'government_level' => env('GOVERNMENT_LEVEL', 'federal'),
    'current_congress' => (int) env('CURRENT_CONGRESS', 119),
    'chamber' => env('CHAMBER', 'House'),
    'first_elected' => env('FIRST_ELECTED'),

    'dc_office' => [
        'name' => env('DC_OFFICE_NAME', 'Washington, DC Office'),
        'address' => env('DC_OFFICE_ADDRESS', ''),
        'city' => env('DC_OFFICE_CITY', 'Washington'),
        'state' => env('DC_OFFICE_STATE', 'DC'),
        'zip' => env('DC_OFFICE_ZIP', ''),
        'phone' => env('DC_OFFICE_PHONE', ''),
        'timezone' => env('DC_OFFICE_TIMEZONE', 'America/New_York'),
        'lat' => env('DC_OFFICE_LAT'),
        'lng' => env('DC_OFFICE_LNG'),
    ],

    'district_offices' => [],
    'district_cities' => [],
    'district_counties' => [],
    'official_website' => env('OFFICIAL_WEBSITE', ''),

    'social_media' => array_filter([
        'twitter' => env('SOCIAL_TWITTER'),
        'facebook' => env('SOCIAL_FACEBOOK'),
        'instagram' => env('SOCIAL_INSTAGRAM'),
        'youtube' => env('SOCIAL_YOUTUBE'),
        'linkedin' => env('SOCIAL_LINKEDIN'),
        'bluesky' => env('SOCIAL_BLUESKY'),
        'tiktok' => env('SOCIAL_TIKTOK'),
    ]),

    'news_sources' => [],

    'congress_api' => [
        'key' => env('CONGRESS_API_KEY'),
        'base_url' => env('CONGRESS_API_BASE_URL', 'https://api.congress.gov/v3'),
        'rate_limit' => (int) env('CONGRESS_API_RATE_LIMIT', 5000),
    ],

    'setup_completed_at' => env('OFFICE_SETUP_COMPLETED_AT'),
    'setup_version' => env('OFFICE_SETUP_VERSION', '1.0.0'),
];
