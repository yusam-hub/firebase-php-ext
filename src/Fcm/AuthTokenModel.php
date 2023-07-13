<?php

namespace YusamHub\FirebasePhpExt\Fcm;

class AuthTokenModel
{
    public ?string $access_token = null;
    public ?int $expires_in = null;
    public ?string $token_type = null;
    protected int $beginTime;
    protected int $endTime;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->assign($properties);
    }

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
}