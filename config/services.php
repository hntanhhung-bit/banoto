<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ghn' => [
        'base_url' => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api'),
        'token' => env('GHN_TOKEN', 'eea1eb4a-aa85-11f1-a973-aee5264794df'),
        'shop_id' => env('GHN_SHOP_ID', 217505),
        'verify_ssl' => env('GHN_VERIFY_SSL', false),
        'from_district_id' => env('GHN_FROM_DISTRICT_ID', 1450),
        'from_ward_code' => env('GHN_FROM_WARD_CODE', '20806'),
        'default_weight' => env('GHN_DEFAULT_WEIGHT', 25000), // Mặc định 25kg (25000g) cho mặt hàng xe
    ],

    'momo' => [
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'),
        'access_key' => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),
        'secret_key' => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'),
        'verify_ssl' => env('MOMO_VERIFY_SSL', false),
        'redirect_url' => env('MOMO_REDIRECT_URL'),
        'ipn_url' => env('MOMO_IPN_URL'),
    ],

    'sepay' => [
        'api_key' => env('SEPAY_API_KEY', '2ZABJEF6XREZPY7VT5QNN0BQGCCEUIVGZIMFGMCWMSOYUOF7SIXWH89YETJ5NPYL'),
        'bank_acc' => env('SEPAY_BANK_ACC', '12325072005'),
        'bank_name' => env('SEPAY_BANK_NAME', 'TPBank'),
        'acc_name' => env('SEPAY_ACC_NAME', 'HOANG NGOC THI'),
        'bank_bin' => env('SEPAY_BANK_BIN', '970423'),
        'api_endpoint' => env('SEPAY_API_ENDPOINT', 'https://my.sepay.vn/userapi'),
    ],

];
