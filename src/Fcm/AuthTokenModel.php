<?php

namespace YusamHub\FirebasePhpExt\Fcm;

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
    protected int $beginTime = 0;
    protected int $endTime = 0;

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
        $this->beginTime = time();
        $this->endTime =  $this->beginTime + intval($this->expires_in);
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return time() > $this->endTime - 60;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'beginTime' => $this->beginTime,
            'endTime' => $this->endTime,
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
        return 'Bearer ' . $this->access_token;
    }
}