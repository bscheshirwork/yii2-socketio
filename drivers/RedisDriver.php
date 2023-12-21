<?php

namespace bscheshirwork\socketio\drivers;

use Predis\Client;
use yii\helpers\ArrayHelper;

/**
 * @todo Implement username and password
 *
 * Class RedisDriver
 */
final class RedisDriver
{
    public string $hostname = 'localhost';

    public int $port = 6379;

    public string $password;

    private ?Client $connection = null;

    /**
     * Get predis connection
     */
    public function getConnection($reset = false): ?Client
    {
        if ($this->connection === null || $reset === true) {
            $this->connection = new Client(ArrayHelper::merge([
                'scheme' => 'tcp',
                'read_write_timeout' => 0,
            ], [
                'host' => $this->hostname,
                'port' => $this->port,
                'password' => $this->password,
            ]));
        }

        return $this->connection;
    }
}
