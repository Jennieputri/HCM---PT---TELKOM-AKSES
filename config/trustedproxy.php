<?php

$configuredProxies = env('TRUSTED_PROXIES');

return [

    'proxies' => in_array($configuredProxies, ['*', '**'], true) ? null : $configuredProxies,

];
