<?php
/**
 * PayPal Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */

return [
    'mode'    => 'sandbox', // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
    'sandbox' => [
        'username'    => env('PAYPAL_SANDBOX_API_USERNAME', 'lile.gjorgjievska@devsy.com'),
        'password'    => env('PAYPAL_SANDBOX_API_PASSWORD', 'Desvy@123!'),
        'secret'      => env('PAYPAL_SANDBOX_API_SECRET', 'EFsTeN1Z9o_HPB1R_t5lHC8STSxbrk1dB8UjksKg6plW9B2n6PBk_MAmfYs4EEaSm6VZqrFpKP0gAX4w'),
        'certificate' => env('PAYPAL_SANDBOX_API_CERTIFICATE', ''),
        'app_id'      => 'AVwMhWv88r4ReBv28noRG6AOMKvEo0_IwHpDi4GdicMUOKQJjv1lnh5s6ltCQfPu_riZP7U31_WjMsZB', // Used for testing Adaptive Payments API in sandbox mode
    ],
    'live' => [
        'username'    => env('PAYPAL_LIVE_API_USERNAME', ''),
        'password'    => env('PAYPAL_LIVE_API_PASSWORD', ''),
        'secret'      => env('PAYPAL_LIVE_API_SECRET', ''),
        'certificate' => env('PAYPAL_LIVE_API_CERTIFICATE', ''),
        'app_id'      => '', // Used for Adaptive Payments API
    ],

    'payment_action' => 'Sale', // Can only be 'Sale', 'Authorization' or 'Order'
    'currency'       => 'USD',
    'billing_type'   => 'MerchantInitiatedBilling',
    'notify_url'     => '', // Change this accordingly for your application.
    'locale'         => '', // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'validate_ssl'   => true, // Validate SSL when creating api client.
];
