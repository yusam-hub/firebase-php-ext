<?php

namespace YusamHub\FirebasePhpExt\Fcm;

use GuzzleHttp\Psr7\Query;

class AuthTokenModel
{
    protected static ?AuthTokenModel $instance = null;

    public static function Instance(): AuthTokenModel
    {
        if (is_null(self::$instance)) {
            self::$instance = new AuthTokenModel();
        }
        return self::$instance;
    }

    protected ?string $access_token = null;
    protected ?int $expires_in = null;
    protected ?string $token_type = null;
    protected ?int $beginTime = null;
    protected ?int $endTime = null;

    /**
     * @param array $properties
     * @return void
     */
    public function assign(array $properties): void
    {
        foreach($properties as $property => $v) {
            if (property_exists($this, $property)) {
                $this->{$property} = $v;
            }
        }
        if (is_null($this->beginTime)) {
            $this->beginTime = time();
        }
        if (is_null($this->endTime)) {
            $this->endTime = $this->beginTime + intval($this->expires_in);
        }
    }

    public function reset()
    {
        $this->access_token = null;
        $this->expires_in = null;
        $this->token_type = null;
        $this->beginTime = null;
        $this->endTime = null;
    }

    /**
     * @return int
     */
    public function getExpiredEndTime(): int
    {
        return $this->endTime;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'beginTime' => $this->beginTime,
            'endTime' => $this->endTime,
            'beginTimeFormatted' => date("Y-m-d H:i:s",$this->beginTime),
            'endTimeFormatted' => date("Y-m-d H:i:s", $this->endTime),
            'token_type' => $this->token_type,
            'expires_in' => $this->expires_in,
            'access_token' => $this->access_token,
        ];
    }

    /**
     * @return string
     */
    public function getAuthorizationHeaderValue(): string
    {
        return ucfirst($this->token_type) . ' ' . $this->access_token;
    }

    /**
     * @param ServiceAccountModel $serviceAccountModel
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchAuthToken(ServiceAccountModel $serviceAccountModel): bool
    {
        $client = new \GuzzleHttp\Client(['timeout' => 20]);
        $response = $client->request('POST', $serviceAccountModel->getTokenUrl(), [
            'headers' => [
                'Cache-Control' => 'no-store',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => Query::build([
                'grant_type' => $serviceAccountModel::JWT_URN,
                'assertion' => $serviceAccountModel->toJwt()
            ])
        ]);
        if ($response->getStatusCode() === 200) {
            $this->reset();
            $this->assign(@json_decode($response->getBody()->getContents(), true));
            return true;
        }
        return false;
    }
}