<?php

namespace YusamHub\FirebasePhpExt\Fcm;

use Kreait\Firebase\Exception\InvalidArgumentException;

class FcmBatchModel
{
    public const MAX_AMOUNT_OF_MESSAGES = 500;
    public const FCM_BATCH_URL = 'https://fcm.googleapis.com/batch';
    protected string $boundary;
    protected array $headers;
    protected array $messageBodies;

    protected ServiceAccountModel $serviceAccountModel;
    /**
     *
     */
    public function __construct(ServiceAccountModel $serviceAccountModel)
    {
        $this->serviceAccountModel = $serviceAccountModel;
        $this->boundary = sha1(uniqid('', true));
        $this->headers = [
            'Content-Type' => 'multipart/mixed; boundary='.$this->boundary,
        ];
    }

    /**
     * @param array $messageBody
     * @return void
     */
    public function addMessageBody(array $messageBody): void
    {
        if (count($this->messageBodies)+1 > self::MAX_AMOUNT_OF_MESSAGES) {
            throw new InvalidArgumentException('Only '.self::MAX_AMOUNT_OF_MESSAGES.' messages can be add for send at a time.');
        }
        $this->messageBodies[] = $messageBody;
    }

    /**
     * @return array|string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function generateBody(): string
    {
        $body = "";
        $index = 0;
        foreach ($this->messageBodies as $message) {
            $body .= "--{$this->boundary}\r\n";
            $body .= $this->generateHeadersAsString([
                    'Content-ID' => (string) ++$index,
                    'Content-Transfer-Encoding' => 'binary',
                    'Content-Type' => 'application/http',
                ])."\r\n\r\n";
            $body .= "POST {$this->serviceAccountModel->getFcmProjectsMessagesSendUrl()} HTTP/1.1}\r\n\r\n";
            $body .= $this->generateHeadersAsString([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])."\r\n\r\n";
            $body .= json_encode($message)."\r\n";
        }
        $body .= "--{$this->boundary}--";

        return $body;
    }

    /**
     * @param array $headers
     * @return string
     */
    protected function generateHeadersAsString(array $headers): string
    {
        $headerNames = array_keys($headers);

        $lineHeaders = [];

        foreach ($headerNames as $name => $value) {
            if (mb_strtolower($name) === 'host') {
                continue;
            }
            $lineHeaders[] = "{$name}: {$value}";
        }

        return implode("\r\n", $lineHeaders);
    }

}