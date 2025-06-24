<?php 
return [
    // Base endpoint (without trailing slash)
    'base_url'     => env('DREAMSMS_BASE_URL', 'https://www.dreams.sa/index.php/api'),
    // Your API account credentials
    'account_username'         => env('DREAMSMS_USER'),
    'secret_key'   => env('DREAMSMS_SECRET_KEY'),
    // OAuth2 client credentials (for token/generate)
    'client_id'    => env('DREAMSMS_CLIENT_ID'),
    'client_secret'=> env('DREAMSMS_CLIENT_SECRET'),
    'sender_name'  => env('DREAMSMS_SENDER_NAME'),
];
