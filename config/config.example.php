<?php

return [
    'serviceAccountFile' => __DIR__ . '/test-account.json',
    'logFile' => __DIR__ .'/../tmp/firebase-php-ext.log',
    'logDebugFile' => __DIR__ .'/../tmp/firebase-php-ext-debug.log',
    'legacy' => [
        'serverUrl' => 'https://fcm.googleapis.com/fcm/send',
        'serverKey' => '',
    ],
    'toDeviceToken' => '',
];