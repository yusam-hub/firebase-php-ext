<?php

use GuzzleHttp\Psr7\Query;
use YusamHub\FirebasePhpExt\FirebasePhpReact;

require_once(__DIR__ . "/../vendor/autoload.php");

$loop = \React\EventLoop\Loop::get();

$timer = $loop->addPeriodicTimer(0.1, function ($timer) use ($loop) {
    $loop->cancelTimer($timer);

    echo sprintf('Start call at [ %d ]', date("Y-m-d H:i:s")) . PHP_EOL;

    $config = require __DIR__ . '/../config/config.php';

    $firebasePhpReact = new FirebasePhpReact($config);
    $firebasePhpReact->testReactPhp($loop);

    /*$browser = new \React\Http\Browser();

    $browser
        ->request(
            'GET',
            'https://ya.ru',
            [

            ],
            ''
        )
        ->then(
            function (\Psr\Http\Message\ResponseInterface $response) use($loop, $timer) {
                var_dump([
                    'getStatus' => $response->getStatusCode(),
                    'body' => (string) $response->getBody(),
                ]);
                $loop->stop();
            },
            function (\Exception $e) use($loop, $timer) {
                var_dump($e->getMessage());
                $loop->stop();
            }
        );*/
});

$stop_func = function ($signal) use ($loop, &$timer, &$stop_func) {
    echo sprintf('Unix signal [ %d ]', $signal) . PHP_EOL;
    $loop->cancelTimer($timer);
    $loop->stop();
};

$loop->addSignal(SIGTERM, $stop_func);

$loop->run();