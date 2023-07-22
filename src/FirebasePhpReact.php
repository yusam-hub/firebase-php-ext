<?php

namespace YusamHub\FirebasePhpExt;

use React\Http\Browser;
use React\Http\Message\ResponseException;
use YusamHub\FirebasePhpExt\Fcm\AuthTokenModel;
use YusamHub\FirebasePhpExt\Fcm\ServiceAccountModel;

class FirebasePhpReact
{
    const ERROR_STRING_INVALID_TOKEN = 'The registration token is not a valid FCM registration token';
    protected ServiceAccountModel $serviceAccountModel;
    protected AuthTokenModel $authTokenModel;

    /**
     * @param ServiceAccountModel $serviceAccountModel
     * @param AuthTokenModel $authTokenModel
     */
    public function __construct(
        ServiceAccountModel $serviceAccountModel,
        AuthTokenModel $authTokenModel
    )
    {
        $this->serviceAccountModel = $serviceAccountModel;
        $this->authTokenModel = $authTokenModel;
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
        $browser
            ->request('POST', $this->serviceAccountModel->getFcmProjectsMessagesSendUrl(),
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->authTokenModel->getAuthorizationHeaderValue(),
                ],
                $body
            )
            ->then(
                $onResponse,
                $onFail
            );
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