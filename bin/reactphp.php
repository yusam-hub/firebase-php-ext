<?php

use GuzzleHttp\Psr7\Query;
use YusamHub\FirebasePhpExt\FirebasePhpReact;

require_once(__DIR__ . "/../vendor/autoload.php");

$loop = \React\EventLoop\Loop::get();

$timer = $loop->addPeriodicTimer(0.1, function ($timer) use ($loop) {
    $loop->cancelTimer($timer);

    echo sprintf('Start call at [ %d ]', date("Y-m-d H:i:s")) . PHP_EOL;

    $config = require __DIR__ . '/../config/config.php';

    $firebasePhpReact = new FirebasePhpReact(new \YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel($config));
    $firebasePhpReact->testReactPhp($loop);

});

$stop_func = function ($signal) use ($loop, &$timer, &$stop_func) {
    echo sprintf('Unix signal [ %d ]', $signal) . PHP_EOL;
    $loop->cancelTimer($timer);
    $loop->stop();
};

$loop->addSignal(SIGTERM, $stop_func);

$loop->run();