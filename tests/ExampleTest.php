<?php

namespace YusamHub\FirebasePhpExt\Tests;

use Google\Auth\OAuth2;
use GuzzleHttp\Exception\GuzzleException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use YusamHub\FirebasePhpExt\FirebasePhpExt;
use YusamHub\FirebasePhpExt\FirebasePhpLegacy;
use YusamHub\FirebasePhpExt\FirebasePhpReact;

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

        $firebasePhpReact = new FirebasePhpReact($config);
        $firebasePhpReact->cloudMessageSend(
            $config['toDeviceToken'],
            $message
        );

        /*$firebasePhpExt = new FirebasePhpExt($config);
        $ret = $firebasePhpExt->cloudMessageSend(
            $config['toDeviceToken'],
            $message
        );
        var_dump($ret);*/

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