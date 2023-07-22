<?php

require_once(__DIR__ . "/../vendor/autoload.php");

\YusamHub\FirebasePhpExt\Fcm\AuthTokenStorage::Instance()->setFilename(
    __DIR__ . '/../tmp/token.json'
);
\YusamHub\FirebasePhpExt\Fcm\AuthTokenStorage::Instance()->waitWhileTokenIsValid(function(string $message){
    echo $message . PHP_EOL;
});