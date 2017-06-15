<?php

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;

return [
    // Session configuration.
    'config' => [
        'cookie_lifetime' => 60 * 60 * 1,
        'gc_maxlifetime' => 60 * 60 * 24 * 30,
        'cache_expire' => 60 * 2,
        'cookie_domain' => '.manylinks.online',
        'cookie_httponly' => true,
        'name' => 'ml',
        'use_cookies' => true,

    ],
    // Session manager configuration.
    'manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'storage' => SessionArrayStorage::class,
];