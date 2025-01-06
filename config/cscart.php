<?php

return [
    'api_base' => env('CSCART_API_BASE', 'http://cscart.dinfo/api.php?_d='),
    'username' => env('CSCART_USERNAME', 'csadmin@mailinator.com'),
    'api_key' => env('CSCART_API_KEY', '8VV4x3C2t0lN3pCYf60ZphRF76WWm1VM'),

    'get_product_url' => 'products/:product_id:',
    'create_product_url' => 'products',
    'update_product_url' => 'products/:product_id:',
    'delete_product_url' => 'products/:product_id:',

    'generate_product_variations' => 'product_variations/:product_id:/generate_product_variations',
];
