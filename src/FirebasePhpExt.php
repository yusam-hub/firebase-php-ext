<?php

namespace YusamHub\FirebasePhpExt;

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Http\HttpClientOptions;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class FirebasePhpExt
{
    protected string $serviceAccountFile = '';
    protected string $logFile = '';
    protected string $logDebugFile = '';
    protected int $timeout = 20;
    protected Factory $factory;
    protected Messaging $messaging;

    public function __construct(array $config = [])
    {
        foreach($config as $k => $v) {
            if (property_exists($this, $k)) {
                $this->{$k} = $v;
            }
        }

        $httpLogger = new Logger('firebase_http_logs');
        $httpLogger->pushHandler(new StreamHandler($this->logFile));

        $httpDebugLogger = new Logger('firebase_http_debug_logs');
        $httpDebugLogger->pushHandler(new StreamHandler($this->logDebugFile));

        $options = HttpClientOptions::default();
        $options = $options->withTimeOut($this->timeout);

        $this->factory = (new Factory())
            ->withHttpLogger($httpLogger, null, 'debug', 'debug')
            ->withHttpDebugLogger($httpDebugLogger, null, 'debug', 'debug')
            ->withHttpClientOptions($options)
            ->withServiceAccount($this->serviceAccountFile)
        ;

        $this->messaging = $this->factory->createMessaging();
    }

    /**
     * @return array
     */
    public function getFactoryDebugInfo(): array
    {
        return $this->factory->getDebugInfo();
    }

    /**
     * @param string $toDeviceToken
     * @param array $data
     * @param bool $validateOnly
     * @return array
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function cloudMessageSend(string $toDeviceToken, array $data = [], bool $validateOnly = false): array
    {
        return $this->messaging->send(CloudMessage::fromArray(
            array_merge($data, [
                'token' => $toDeviceToken
            ])
        ), $validateOnly);
    }

    /**
     * @param array $toDeviceTokens
     * @param array $data
     * @param bool $validateOnly
     * @return MulticastSendReport
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function cloudMessageSendAll(array $toDeviceTokens, array $data = [], bool $validateOnly = false): Messaging\MulticastSendReport
    {
        $messages = [];
        foreach($toDeviceTokens as $toDeviceToken) {
            $messages[] = CloudMessage::fromArray(
                array_merge($data, [
                    'token' => $toDeviceToken
                ])
            );
        }

        return $this->messaging->sendAll($messages, $validateOnly);
    }

    /**
     * @param array $toDeviceTokens
     * @param array $data
     * @param bool $validateOnly
     * @return MulticastSendReport
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function cloudMessageMulticast(array $toDeviceTokens, array $data = [], bool $validateOnly = false): Messaging\MulticastSendReport
    {
        return $this->messaging->sendMulticast(CloudMessage::fromArray($data), $toDeviceTokens, $validateOnly);
    }
}