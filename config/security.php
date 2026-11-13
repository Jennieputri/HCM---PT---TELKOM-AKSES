<?php

return [

    'csp_asset_hosts' => env('SECURITY_CSP_ASSET_HOSTS', 'https://cdn.jsdelivr.net'),

    'csp_image_hosts' => env('SECURITY_CSP_IMAGE_HOSTS', 'https://*.tile.openstreetmap.org https://server.arcgisonline.com'),

    'session_bind_ip' => (bool) env('SECURITY_SESSION_BIND_IP', false),

    'session_absolute_minutes' => (int) env('SECURITY_SESSION_ABSOLUTE_MINUTES', 1440),

    'document_downloads_per_minute' => (int) env('SECURITY_DOCUMENT_DOWNLOADS_PER_MINUTE', 20),

];
