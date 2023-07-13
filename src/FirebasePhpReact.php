<?php

namespace YusamHub\FirebasePhpExt;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use YusamHub\FirebasePhpExt\Fcm\AuthTokenModel;
use YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel;

class FirebasePhpReact
{
    protected ServiceAccountModel $serviceAccountModel;

    /**
     * @param ServiceAccountModel $serviceAccountModel
     */
    public function __construct(ServiceAccountModel $serviceAccountModel)
    {
        $this->serviceAccountModel = $serviceAccountModel;
    }

    public function testReactPhp(LoopInterface $loop): void
    {
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

        $browser = new Browser();
        $browser
            ->request('POST', $this->serviceAccountModel->getTokenUrl(),
            [
                'Cache-Control' => 'no-store',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            Query::build([
                'grant_type' => $this->serviceAccountModel::JWT_URN,
                'assertion' => $this->serviceAccountModel->toJwt()
            ])
        )
        ->then(
            function (ResponseInterface $response) use ($loop, $browser) {
                if ($response->getStatusCode() !== 200) {
                    $loop->stop();
                }

                AuthTokenModel::Instance()->assign((array)json_decode((string)$response->getBody(), true));
                var_dump(AuthTokenModel::Instance()->toArray());

                $browser
                    ->request('POST', $this->serviceAccountModel->getFcmProjectsMessagesSendUrl(),
                        [
                            'Content-Type' => 'application/json',
                            'Authorization' => AuthTokenModel::Instance()->getAuthorizationHeaderValue(),
                        ],
                        '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":false}'
                    )
                    ->then(
                        function (ResponseInterface $response) use ($loop, $browser) {
                            if ($response->getStatusCode() !== 200) {
                                $loop->stop();
                            }
                            $res = (array)json_decode((string)$response->getBody(), true);
                            var_dump($res);
                            $loop->stop();
                        },
                        function (\Exception $e) use ($loop) {
                            var_dump($e->getMessage());
                            $loop->stop();
                        }
                    );
            },
            function (\Exception $e) use ($loop) {
                var_dump($e->getMessage());
                $loop->stop();
            }
        );
    }

    /**
     * @param string $toDeviceToken
     * @param array $data
     * @param bool $validateOnly
     */
    public function cloudMessageSend(string $toDeviceToken, array $data = [], bool $validateOnly = false): void
    {

    }
}