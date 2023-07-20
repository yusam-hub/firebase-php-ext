<?php

namespace YusamHub\FirebasePhpExt;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;
use React\Http\Message\ResponseException;
use YusamHub\FirebasePhpExt\Fcm\AuthTokenModel;
use YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel;

class FirebasePhpReact
{
    const ERROR_STRING_INVALID_TOKEN = 'The registration token is not a valid FCM registration token';
    protected ServiceAccountModel $serviceAccountModel;

    /**
     * @param ServiceAccountModel $serviceAccountModel
     */
    public function __construct(ServiceAccountModel $serviceAccountModel)
    {
        $this->serviceAccountModel = $serviceAccountModel;
    }

    /**
     * @param string $body
     * @param callable $onResponse
     * @param callable $onFail
     * @return void
     */
    protected function fcmMessagesSend(string $body, callable $onResponse, callable $onFail): void
    {
        $browser = new Browser();

        if (AuthTokenModel::Instance()->isExpired()) {
            //если нет токена, получаем его и потом запрос к firebase
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
                    function (ResponseInterface $response) use ($browser, $body, $onResponse, $onFail) {

                        if ($response->getStatusCode() !== 200) {
                            $exception = new \RuntimeException("Invalid response of getting access token request");
                            $onFail($exception);//пока такой вариант, надо узнать как в promise обрабатывается исключения типа throw new \Exception
                            return;
                        }

                        AuthTokenModel::Instance()->assign((array)json_decode((string)$response->getBody(), true));

                        //запрос к firebase
                        $browser
                            ->request('POST', $this->serviceAccountModel->getFcmProjectsMessagesSendUrl(),
                                [
                                    'Content-Type' => 'application/json',
                                    'Authorization' => AuthTokenModel::Instance()->getAuthorizationHeaderValue(),
                                ],
                                $body
                            )
                            ->then(
                                $onResponse,
                                $onFail
                            );
                    },
                    $onFail
                );
        } else {
            //если есть токен, сразу запрос к firebase
            $browser
                ->request('POST', $this->serviceAccountModel->getFcmProjectsMessagesSendUrl(),
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => AuthTokenModel::Instance()->getAuthorizationHeaderValue(),
                    ],
                    $body
                )
                ->then(
                    $onResponse,
                    $onFail
                );
        }
    }

    public function testReactPhp_fcmMessagesSend(LoopInterface $loop): void
    {
        $fcmBodyRequestValidateFalse = '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":false}';
        $fcmBodyRequestValidateTrue = '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';
        $fcmBodyRequestValidateTrueWithoutBody = '{"message":{"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';
        $fcmBodyRequestValidateTrueWithoutBodyInvalidToken = '{"message":{"token":"dr8ZnHpbucuAvkXavpjZxd:1APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":true}';

        $this->fcmMessagesSend($fcmBodyRequestValidateTrueWithoutBodyInvalidToken,
            function (ResponseInterface $response) use ($loop) {
                if ($response->getStatusCode() !== 200) {
                    $loop->stop();
                }
                $res = (array)json_decode((string)$response->getBody(), true);
                var_dump($res);
                $loop->stop();
            },
            function (ResponseException $e) use ($loop) {
                if ($this->isRegistrationTokenInvalid($e)) {
                    var_dump("Invalid registration token");
                } else {
                    var_dump("Error: " . $e->getMessage());
                }
                $loop->stop();
            }
        );
    }

    protected function isRegistrationTokenInvalid(ResponseException $e): bool
    {
        $res = (array)json_decode((string)$e->getResponse()->getBody(), true);
        return ($e->getCode() === 400) && (isset($res['error']['message']) && mb_strstr($res['error']['message'], self::ERROR_STRING_INVALID_TOKEN));
    }
}