<?php

namespace YusamHub\FirebasePhpExt\Fcm;

class ServiceAccountModel
{
    const DEFAULT_EXPIRY_SECONDS = 3600; // 1 hour
    const DEFAULT_SKEW_SECONDS = 60; // 1 minute
    const JWT_URN = 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    protected ?string $serviceAccountFile = null;

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

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
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
            'exp' => ($now + self::DEFAULT_EXPIRY_SECONDS),
            'iat' => ($now - self::DEFAULT_SKEW_SECONDS),
            'aud' => $this->token_uri,
            'scope' => implode(" ", $this->scope),
        ];

        return \Firebase\JWT\JWT::encode(
            $assertion,
            $this->private_key,
            'RS256'
        );
    }

    public function getFcmProjectsMessagesSendUrl(): string
    {
        if (is_null($this->project_id)) {
            throw new \RuntimeException('No project_id available');
        }
        return sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $this->project_id);
    }
}