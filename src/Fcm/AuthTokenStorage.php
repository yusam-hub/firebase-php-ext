<?php

namespace YusamHub\FirebasePhpExt\Fcm;

class AuthTokenStorage
{
    /**
     * WAIT_WHILE_TOKEN_IS_VALID_SECONDS - задержка перед повторным чтением файла,
     */
    const WAIT_WHILE_TOKEN_IS_VALID_SECONDS = 5;
    /**
     * DEADLINE_OF_EXPIRED_TOKEN_SECONDS - если жизнь токена 3600 секунд, а deadline 900, это означает
     *                              что за 15 минут токен будет считаться просроченным, что бы гарантированно
     *                              выполнились операции
     */
    const DEADLINE_OF_EXPIRED_TOKEN_SECONDS = 900;
    /*
     ******************* STATIC *******************
     */
    protected static ?AuthTokenStorage $instance = null;
    public static function Instance(): AuthTokenStorage
    {
        if (is_null(self::$instance)) {
            self::$instance = new AuthTokenStorage();
        }
        return self::$instance;
    }

    /*
     ******************* PROTECTED *******************
     */

    protected string $filename = '';

    /*
     ******************* METHODS *******************
     */

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @param string $data
     * @return bool
     */
    protected function writeData(string $data): bool
    {
        if (!file_exists($this->filename)) {
            touch($this->filename);
        }
        $fd = fopen($this->filename, 'r+');
        if ($fd === false) {
            return false;
        }
        $res = false;
        if (flock($fd, LOCK_EX)) // установка исключительной блокировки на запись
        {
            fseek($fd, 0); //переход в начала файла
            $n = fwrite($fd, $data);
            if ($n !== false) {
                $res = true;
            }
            flock($fd, LOCK_UN); // снятие блокировки
        }
        fclose($fd);
        return $res;
    }

    /**
     * @return string|null
     */
    protected function readData(): ?string
    {
        if (!file_exists($this->filename)) {
            return null;
        }
        $fd = fopen($this->filename, 'r+');
        if ($fd === false) {
            return null;
        }
        $data = null;
        if (flock($fd, LOCK_EX)) // установка исключительной блокировки на запись
        {
            fseek($fd, 0); //переход в начала файла
            $data = (string) fread($fd, filesize($this->filename));
            flock($fd, LOCK_UN); // снятие блокировки
        }
        fclose($fd);
        return $data;
    }

    /**
     * @param AuthTokenModel $authTokenModel
     * @return void
     */
    public function writeValidToken(AuthTokenModel $authTokenModel): void
    {
        $this->writeData(json_encode($authTokenModel->toArray()));
    }

    /**
     * @param callable|null $message
     * @param int $deadlineOfExpiredTokenSeconds
     * @param int $waitWhileTokenIsValidSeconds
     * @return AuthTokenModel
     */
    public function waitWhileTokenIsValid(
        callable $message,
        int $deadlineOfExpiredTokenSeconds = self::DEADLINE_OF_EXPIRED_TOKEN_SECONDS,
        int $waitWhileTokenIsValidSeconds = self::WAIT_WHILE_TOKEN_IS_VALID_SECONDS
    ): AuthTokenModel
    {
        while (true) {
            //ждем когда появится файл, если его еще не было
            if (file_exists($this->filename)) {
                $message(sprintf('[%s] reading token from file [ %s ]', date("Y-m-d H:i:s"), $this->filename));
                $properties = (array) @json_decode($this->readData(), true);
                $authTokenModel = new AuthTokenModel();
                $authTokenModel->assign($properties);
                //если ключ токена жив еще и нам хватит времени его использовать, то выходим, иначе ждем когда появится новый токен
                if (time() < $authTokenModel->getExpiredEndTime() - $deadlineOfExpiredTokenSeconds) {
                    $message(sprintf('[%s] token is valid', date("Y-m-d H:i:s")));
                    $res = $authTokenModel;
                    break;
                } else {
                    $message(sprintf('[%s] token is invalid', date("Y-m-d H:i:s")));
                }
            } else {
                $message(sprintf('[%s] token file [ %s ] not exists', date("Y-m-d H:i:s"), $this->filename));
            }
            $message(sprintf('[%s] wait [ %d ] seconds for valid token', date("Y-m-d H:i:s"), $waitWhileTokenIsValidSeconds));
            sleep($waitWhileTokenIsValidSeconds);
        }
        return $res;
    }

}