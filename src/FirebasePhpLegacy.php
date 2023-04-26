<?php

namespace YusamHub\FirebasePhpExt;

use GuzzleHttp\Exception\GuzzleException;
use http\Client\Response;
use Psr\Http\Message\ResponseInterface;

class FirebasePhpLegacy
{
    protected string $serverUrl = '';
    protected string $serverKey = '';

    protected \GuzzleHttp\Client $client;
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
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @param array|string $to
     * @param array $data
     * @param bool $validateOnly
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function cloudMessageSend(array|string $to, array $data = [], bool $validateOnly = false): ResponseInterface
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=' . $this->serverKey
        ];

        $senderData = [];
        if (is_string($to)) {
            $senderData['to'] = $to;
        } elseif (is_array($to)) {
            $senderData['registration_ids'] = $to;
        }

        if ($validateOnly === true) {
            $data = array_merge([
                'dry_run' => true,
            ], $senderData);
        } else {
            $data = array_merge($data, $senderData);
        }

        return $this->client->request('POST', $this->serverUrl, [
            'headers' => $headers,
            'body' => json_encode($data)
        ]);
    }
}