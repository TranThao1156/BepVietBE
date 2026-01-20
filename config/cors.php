<?php

return [

   
    // 1. Áp dụng cho các route bắt đầu bằng api/ và route của sanctum
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 2. Cho phép tất cả các method (GET, POST, PUT, DELETE...)
    'allowed_methods' => ['*'],

    // 3. QUAN TRỌNG: Chỉ định rõ địa chỉ Frontend React của bạn
    // Không nên dùng '*' khi supports_credentials = true
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    // 4. Cho phép gửi mọi loại Header (bao gồm Authorization để chứa Token)
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 5. Cho phép xác thực (Cookie/Token)
    'supports_credentials' => true,

];