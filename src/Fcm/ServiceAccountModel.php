<?php

namespace YusamHub\FirebasePhpExt\Fcm;

class ServiceAccountModel
{
    const DEFAULT_EXPIRE_SECONDS = 3600; // 1 hour
    const DEFAULT_SKEW_SECONDS = 60; // 1 minute
    const JWT_URN = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    protected ?string $serviceAccountFile = null;
    protected ?string $storageTokenFile = null;
    protected ?int $tokenSkewSeconds = null;
    protected ?int $tokenExpireSeconds = null;
    protected ?string $private_key = null;
    protected ?string $client_email = null;
    protected ?string $project_id = null;
    protected ?string $token_uri = null;

    protected array $scope = [
        'https://www.googleapis.com/auth/iam',
        'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/firebase',
        'https://www.googleapis.com/auth/firebase.database',
        'https://www.googleapis.com/auth/firebase.messaging',
        'https://www.googleapis.com/auth/firebase.remoteconfig',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/securetoken',
    ];

    protected static ?ServiceAccountModel $instance = null;

    public static function Instance(): ?ServiceAccountModel
    {
        return self::$instance;
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!is_null(self::$instance)) {
            throw new \RuntimeException(sprintf("Only one instance [%s] can be at once", get_class($this)));
        }
        self::$instance = $this;

        foreach($config as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        $serviceAccount = (array) json_decode(file_get_contents($this->serviceAccountFile), true);
        foreach($serviceAccount as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * @return string
     */
    public function toJwt(): string
    {
        if (is_null($this->client_email)) {
            throw new \RuntimeException('No client_email available');
        }
        if (is_null($this->private_key)) {
            throw new \RuntimeException('No private_key available');
        }
        if (is_null($this->token_uri)) {
            throw new \RuntimeException('No token_uri available');
        }

        $now = time();

        $assertion = [
            'iss' => $this->client_email,
            'exp' => ($now + $this->tokenExpireSeconds??self::DEFAULT_EXPIRE_SECONDS),
            'iat' => ($now - $this->tokenSkewSeconds??self::DEFAULT_SKEW_SECONDS),
            'aud' => $this->token_uri,
            'scope' => implode(" ", $this->scope),
        ];

        return \Firebase\JWT\JWT::encode(
            $assertion,
            $this->private_key,
            'RS256'
        );
    }

    /**
     * @return string
     */
    public function getFcmProjectsMessagesSendUrl(): string
    {
        if (is_null($this->project_id)) {
            throw new \RuntimeException('No project_id available');
        }
        return sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->project_id);
    }

    /**
     * @return string|null
     */
    public function getTokenUrl(): ?string
    {
        return $this->token_uri;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return strval($this->project_id);
    }

    /**
     * @return string|null
     */
    public function getStorageTokenFile(): ?string
    {
        return $this->storageTokenFile;
    }


}