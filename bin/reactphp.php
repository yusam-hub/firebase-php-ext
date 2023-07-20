<?php

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ResponseException;
use YusamHub\FirebasePhpExt\FirebasePhpReact;

require_once(__DIR__ . "/../vendor/autoload.php");

$loop = \React\EventLoop\Loop::get();

$timer = $loop->addPeriodicTimer(0.1, function ($timer) use ($loop) {
    $loop->cancelTimer($timer);

    echo sprintf('Start call at [ %d ]', date("Y-m-d H:i:s")) . PHP_EOL;

    $config = require __DIR__ . '/../config/config.php';

    new \YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel($config);
    $firebasePhpReact = new FirebasePhpReact(\YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel::Instance());

    //$fcmBodyRequestValidate = '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":false}';
    //$fcmBodyRequestValidate = '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';
    //$fcmBodyRequestValidate = '{"message":{"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';
    $fcmBodyRequestValidate = '{"message":{"token":"dr8ZnHpbucuAvkXavpjZxd:1APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';

    $firebasePhpReact->fcmMessagesSend($fcmBodyRequestValidate,
        function (ResponseInterface $response) use ($loop) {
            if ($response->getStatusCode() !== 200) {
                $loop->stop();
            }
            $res = (array)json_decode((string)$response->getBody(), true);
            var_dump($res);
            $loop->stop();
        },
        function (ResponseException $e) use ($loop) {
            if (FirebasePhpReact::isRegistrationTokenInvalid($e)) {
                var_dump("Invalid registration token");
            } else {
                var_dump("Error: " . $e->getMessage());
            }
            $loop->stop();
        }
    );

});

$stop_func = function ($signal) use ($loop, &$timer, &$stop_func) {
    echo sprintf('Unix signal [ %d ]', $signal) . PHP_EOL;
    $loop->cancelTimer($timer);
    $loop->stop();
};

$loop->addSignal(SIGTERM, $stop_func);

$loop->run();