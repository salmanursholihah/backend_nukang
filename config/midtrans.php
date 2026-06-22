<?php
return [
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds'        => env('MIDTRANS_IS_3DS', true),


    // =========================================================================
    // IRIS / DISBURSEMENT
    // Dipakai untuk: withdrawal / payout ke rekening tukang
    // Dashboard: https://app.sandbox.midtrans.com/iris (sandbox)
    //            https://app.midtrans.com/iris         (production)
    // =========================================================================
    'iris_api_key'      => env('MIDTRANS_IRIS_API_KEY'),

    // Approver PIN — diset di Iris dashboard > Settings > Approver PIN
    // Dipakai saat approvePayout() jika akun Iris pakai 2-step approval
    'iris_merchant_key' => env('MIDTRANS_IRIS_MERCHANT_KEY'),

    // Base URL otomatis mengikuti MIDTRANS_IS_PRODUCTION
    'iris_base_url'     => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/iris/api/v1'
        : 'https://app.sandbox.midtrans.com/iris/api/v1',

];
