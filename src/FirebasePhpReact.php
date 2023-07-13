<?php

namespace YusamHub\FirebasePhpExt;

use Google\Auth\CredentialsLoader;
use Google\Auth\OAuth2;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Http\Browser;

class FirebasePhpReact
{
    const DEFAULT_EXPIRY_SECONDS = 3600; // 1 hour
    const DEFAULT_SKEW_SECONDS = 60; // 1 minute
    const JWT_URN = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

    protected ?string $serviceAccountFile = null;

    protected ?string $private_key = null;
    protected ?string $private_key_id = null;
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
    protected array $additionalClaims = [];

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
        if (is_null($this->private_key)) {
            throw new \DomainException('No signing key available');
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
            ->request('POST', $this->token_uri,
            [
                'Cache-Control' => 'no-store',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            Query::build([
                'grant_type' => self::JWT_URN,
                'assertion' => $this->toJwt()
            ])
        )
        ->then(
            function (ResponseInterface $response) use ($loop, $browser) {
                if ($response->getStatusCode() !== 200) {
                    $loop->stop();
                }

                $res = (array)json_decode((string)$response->getBody(), true);
                var_dump($res);

                $browser
                    ->request('POST', 'https://fcm.googleapis.com/v1/projects/kmapushnew/messages:send',
                        [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $res['access_token']
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

        /*try {
            $res = $client->request('POST', 'https://fcm.googleapis.com/v1/projects/kmapushnew/messages:send', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $res['access_token']
                ],
                'body' => '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":false}'
            ]);

            var_dump((string) $res->getBody());
        } catch (\Throwable $e) {
            var_dump((string) $e->getMessage());
        }*/
    }

    /**
     * @param string $toDeviceToken
     * @param array $data
     * @param bool $validateOnly
     */
    public function cloudMessageSend(string $toDeviceToken, array $data = [], bool $validateOnly = false): void
    {
        $client = new \GuzzleHttp\Client(['timeout' => 20]);
        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->toJwt()
        ];
        $res = $client->request('POST', $this->token_uri, [
            'headers' => [
                'Cache-Control' => 'no-store',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => Query::build($params)
        ]);
        $res = (array) json_decode((string) $res->getBody(), true);

        /*$auth = new OAuth2([
            'audience' => $this->token_uri,
            'tokenCredentialUri' => $this->token_uri,
            'issuer' => $this->client_email,
            'sub' => $this->client_email,
            'signingAlgorithm' => 'RS256',
            'signingKey' => $this->private_key,
            'scope' => $this->scope,
        ]);
        $res = $auth->fetchAuthToken();*/

        try {
            $res = $client->request('POST', 'https://fcm.googleapis.com/v1/projects/kmapushnew/messages:send', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $res['access_token']
                ],
                'body' => '{"message":{"data":{"title":"title test account","body":"body test account","icon":"https:\/\/localhost","image":"https:\/\/localhost","click_action":"https:\/\/localhost","actions":"[{\"title\":\"buttonTitle1\",\"action\":\"button1\"},{\"title\":\"buttonTitle2\",\"action\":\"button2\"}]"},"token":"dr8ZnHpbucuAvkXavpjZxd:APA91bFV2ljpYg3Jwa_MWlmRqloGJVqgJEAZn8LYebmcWUkhFRbiRtD9pkfFYmASTwFPL3eyZqrCTOOqFtEc6nTUHEIn8RBMVqMXp88pO-Y4E2pbtIyNFVu4uIqrD3JGvV4gaAsLIZIT"},"validate_only":false}'
            ]);

            var_dump((string) $res->getBody());
        } catch (\Throwable $e) {
            var_dump((string) $e->getMessage());
        }


        /*$browser = new Browser();
        $browser
            ->request('POST','https://fcm.googleapis.com/v1/projects/kmapushnew/messages:send',
            [
                'Authorization' => 'Bearer ' . $res['access_token'],
            ],
            json_encode($data)
        )
        ->then(
            function (ResponseInterface $response)
            {
                var_dump([
                    'getStatus' => $response->getStatusCode(),
                    'body' => (string)$response->getBody(),
                ]);
            },
            function (\Exception $e) {
                var_dump($e->getMessage());
            }
        );*/

        /*$body = (string)$resp->getBody();
        if ($resp->hasHeader('Content-Type') &&
            $resp->getHeaderLine('Content-Type') == 'application/x-www-form-urlencoded'
        ) {
            $res = [];
            parse_str($body, $res);
        }*/
    }
}