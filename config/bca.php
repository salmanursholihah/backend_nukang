<?php

return [
    'client_id'     => env('BCA_CLIENT_ID'),
    'client_secret' => env('BCA_CLIENT_SECRET'),
    'api_key'       => env('BCA_API_KEY'),
    'api_secret'    => env('BCA_API_SECRET'),
    'corp_id'       => env('BCA_CORP_ID', 'BCAAPI2016'),
    'base_url'      => env('BCA_BASE_URL', 'https://sandbox.bca.co.id'),
    'timezone'      => 'Asia/Jakarta',
    'token_ttl'     => 3500,
    'debug'         => env('BCA_DEBUG', false), // FIX: hapus komentar sisa tutorial
];
