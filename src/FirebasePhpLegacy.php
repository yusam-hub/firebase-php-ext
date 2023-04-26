<?php

namespace YusamHub\FirebasePhpExt;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class FirebasePhpLegacy
{
    protected string $serverUrl = '';
    protected string $serverKey = '';
    protected int $timeout = 20;
    protected \GuzzleHttp\Client $client;

    protected array $headers;

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

        $this->client = new \GuzzleHttp\Client(['timeout' => $this->timeout]);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=' . $this->serverKey
        ];
    }

    /**
     * @param string $toDeviceToken
     * @param array $data
     * @param bool $validateOnly
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function cloudMessageSend(string $toDeviceToken, array $data = [], bool $validateOnly = false): ResponseInterface
    {
        $senderData = [];
        $senderData['to'] = $toDeviceToken;

        if ($validateOnly === true) {
            $data = array_merge([
                'dry_run' => true,
            ], $senderData);
        } else {
            $data = array_merge($data, $senderData);
        }

        return $this->client->request('POST', $this->serverUrl, [
            'headers' => $this->headers,
            'body' => json_encode($data)
        ]);
    }

    /**
     * @param array $toDeviceTokens
     * @param array $data
     * @param bool $validateOnly
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function cloudMessageMulticast(array $toDeviceTokens, array $data = [], bool $validateOnly = false): ResponseInterface
    {
        $senderData = [];
        $senderData['registration_ids'] = $toDeviceTokens;

        if ($validateOnly === true) {
            $data = array_merge([
                'dry_run' => true,
            ], $senderData);
        } else {
            $data = array_merge($data, $senderData);
        }

        return $this->client->request('POST', $this->serverUrl, [
            'headers' => $this->headers,
            'body' => json_encode($data)
        ]);
    }
}