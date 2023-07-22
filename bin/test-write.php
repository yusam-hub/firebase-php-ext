<?php

require_once(__DIR__ . "/../vendor/autoload.php");

$config = require __DIR__ . '/../config/config.php';

new \YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel($config);

$authTokenModel = new \YusamHub\FirebasePhpExt\Fcm\AuthTokenModel();
$authTokenModel->fetchAuthToken(\YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel::Instance());

\YusamHub\FirebasePhpExt\Fcm\AuthTokenStorage::Instance()->setFilename(
    __DIR__ . '/../tmp/token.json'
);
\YusamHub\FirebasePhpExt\Fcm\AuthTokenStorage::Instance()->writeValidToken($authTokenModel);

exit("written");