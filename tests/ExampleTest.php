<?php

namespace YusamHub\FirebasePhpExt\Tests;

use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use YusamHub\FirebasePhpExt\FirebasePhpExt;
use YusamHub\FirebasePhpExt\FirebasePhpLegacy;

class ExampleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws GuzzleException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function testDefault()
    {
        $config = require __DIR__ . '/../config/config.php';

        $message = [
            'data' => [
                'title' => 'title test account',
                'body' => 'body test account',
                'icon' => 'https://localhost',
                'image' => 'https://localhost',
                'click_action' => 'https://localhost',
                'actions' => json_encode([
                    [
                        'title' => 'buttonTitle1',
                        'action' => 'button1',
                    ],
                    [
                        'title' => 'buttonTitle2',
                        'action' => 'button2',
                    ],
                ]),
            ]
        ];

        /*$firebasePhpExt = new FirebasePhpExt($config);
        $ret = $firebasePhpExt->cloudMessageSend(
            $config['toDeviceToken'],
            $message
        );
        var_dump($ret);*/

        /*$firebasePhpExt = new FirebasePhpExt($config);
        $ret = $firebasePhpExt->cloudMessageMulticast(
            [
                $config['toDeviceToken'],
            ],
            $message
        );
        var_dump([
            'count' => $ret->count(),
            'hasFailures' => $ret->hasFailures(),
            'invalidTokens' => $ret->invalidTokens(),
            'unknownTokens' => $ret->unknownTokens(),
        ]);*/

        /*$firebasePhpExt = new FirebasePhpExt($config);
        $ret = $firebasePhpExt->cloudMessageSendAll(
            [
                $config['toDeviceToken'],
            ],
            $message
        );
        var_dump([
            'count' => $ret->count(),
            'hasFailures' => $ret->hasFailures(),
            'invalidTokens' => $ret->invalidTokens(),
            'unknownTokens' => $ret->unknownTokens(),
        ]);*/

        /*$firebasePhpLegacy = new FirebasePhpLegacy($config['legacy']);
        $ret = $firebasePhpLegacy->cloudMessageSend(
            $config['toDeviceToken'],
            $message
        );
        var_dump([
            'statusCode' => $ret->getStatusCode(),
            'body' => (string) $ret->getBody(),
        ]);*/

        $this->assertTrue(true);
    }
}