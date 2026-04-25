<?php

/**
 * LegiDash Billing Configuration
 * 
 * Pricing and limits for different office types.
 * During beta, all features are free.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Beta Mode
    |--------------------------------------------------------------------------
    |
    | When true, all offices have free access to all features.
    | Set to false when billing goes live.
    |
    */
    'beta_mode' => env('BILLING_BETA_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | Pricing and limits for each office type.
    |
    */
    'plans' => [
        'local' => [
            'name' => 'Local Government',
            'description' => 'City, County, Municipal',
            'monthly_price' => 2900, // cents
            'annual_price' => 29000, // cents (2 months free)
            'max_users' => 5,
            'ai_requests_monthly' => 250,
            'stripe_monthly_price_id' => env('STRIPE_LOCAL_MONTHLY_PRICE_ID'),
            'stripe_annual_price_id' => env('STRIPE_LOCAL_ANNUAL_PRICE_ID'),
        ],
        'state' => [
            'name' => 'State Legislature',
            'description' => 'State Senators, State Representatives',
            'monthly_price' => 4900, // cents
            'annual_price' => 49000, // cents
            'max_users' => 10,
            'ai_requests_monthly' => 500,
            'stripe_monthly_price_id' => env('STRIPE_STATE_MONTHLY_PRICE_ID'),
            'stripe_annual_price_id' => env('STRIPE_STATE_ANNUAL_PRICE_ID'),
        ],
        'house' => [
            'name' => 'U.S. House',
            'description' => 'Congressional House Offices',
            'monthly_price' => 9900, // cents
            'annual_price' => 99000, // cents
            'max_users' => 25,
            'ai_requests_monthly' => 1000,
            'stripe_monthly_price_id' => env('STRIPE_HOUSE_MONTHLY_PRICE_ID'),
            'stripe_annual_price_id' => env('STRIPE_HOUSE_ANNUAL_PRICE_ID'),
        ],
        'senate' => [
            'name' => 'U.S. Senate',
            'description' => 'Congressional Senate Offices',
            'monthly_price' => 19900, // cents
            'annual_price' => 199000, // cents
            'max_users' => 50,
            'ai_requests_monthly' => 2000,
            'stripe_monthly_price_id' => env('STRIPE_SENATE_MONTHLY_PRICE_ID'),
            'stripe_annual_price_id' => env('STRIPE_SENATE_ANNUAL_PRICE_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Add-ons
    |--------------------------------------------------------------------------
    */
    'addons' => [
        'ai_credits' => [
            'amount' => 500,
            'price' => 1000, // cents ($10)
            'stripe_price_id' => env('STRIPE_AI_CREDITS_PRICE_ID'),
        ],
        'additional_user' => [
            'price_per_month' => 500, // cents ($5/user/month)
            'stripe_price_id' => env('STRIPE_ADDITIONAL_USER_PRICE_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Warnings
    |--------------------------------------------------------------------------
    */
    'warnings' => [
        'ai_warning_threshold' => 0.80, // 80%
        'ai_limit_threshold' => 1.00, // 100%
        'ai_grace_threshold' => 1.10, // 110% (soft block)
    ],

    /*
    |--------------------------------------------------------------------------
    | Features included in all plans
    |--------------------------------------------------------------------------
    */
    'included_features' => [
        'Full access to all features',
        'AI-powered meeting summaries, briefings, and search',
        'Unlimited issues, meetings, contacts, and documents',
        'Calendar integration',
        'Knowledge Hub',
        'Email support',
    ],

    /*
    |--------------------------------------------------------------------------
    | Support Email
    |--------------------------------------------------------------------------
    */
    'support_email' => env('BILLING_SUPPORT_EMAIL', 'billing@legidash.com'),
];

