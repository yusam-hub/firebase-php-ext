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
    public function fcmMessagesSend(string $body, callable $onResponse, callable $onFail): void
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
                            $exception = new ResponseException($response, "Invalid response of getting access token request");
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

    /**
     * @param ResponseException $e
     * @return bool
     */
    public static function isRegistrationTokenInvalid(ResponseException $e): bool
    {
        $res = (array)json_decode((string)$e->getResponse()->getBody(), true);
        return ($e->getCode() === 400) && (isset($res['error']['message']) && mb_strstr($res['error']['message'], self::ERROR_STRING_INVALID_TOKEN));
    }
}