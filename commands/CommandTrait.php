<?php

namespace bscheshirwork\socketio\commands;

use bscheshirwork\socketio\Broadcast;
use Exception;
use JsonException;
use Predis\Connection\ConnectionException;
use Symfony\Component\Process\Process;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;

trait CommandTrait
{
    public string $server = 'locahost:1212';

    /**
     * [
     *     key => 'path to key',
     *     cert => 'path to cert',
     * ]
     */
    public array $ssl = [];

    /**
     * Process job by id and connection
     * @throws JsonException
     */
    public function actionProcess($handler, $data): void
    {
        Broadcast::process($handler, @json_decode((string) $data, true, 512, JSON_THROW_ON_ERROR) ?? []);
    }

    /**
     * @throws JsonException
     */
    public function nodejs(): Process
    {
        // Automatically send every new message to available log routes
        Yii::getLogger()->flushInterval = 1;

        $cmd = [
            'node',
            dirname(__DIR__) . '/server/index.js',
        ];
        $args = array_filter([
            'server' => $this->server,
            'pub' => json_encode(array_filter([
                'host' => Broadcast::getDriver()->hostname,
                'port' => Broadcast::getDriver()->port,
                'password' => Broadcast::getDriver()->password,
            ]), JSON_THROW_ON_ERROR),
            'sub' => json_encode(array_filter([
                'host' => Broadcast::getDriver()->hostname,
                'port' => Broadcast::getDriver()->port,
                'password' => Broadcast::getDriver()->password,
            ]), JSON_THROW_ON_ERROR),
            'channels' => implode(',', Broadcast::channels()),
            'nsp' => Broadcast::getManager()->nsp,
            'ssl' => empty($this->ssl) ? null : json_encode($this->ssl, JSON_THROW_ON_ERROR),
            'runtime' => Yii::getAlias('@runtime/logs'),
        ], 'strlen');
        foreach ($args as $key => $value) {
            $cmd[] = ' -' . $key . '=\'' . $value . '\'';
        }

        return new Process($cmd);
    }

    /**
     * Predis process
     * @throws Exception
     */
    public function predis(): bool
    {
        $pubSubLoop = function (): void {
            $client = Broadcast::getDriver()->getConnection(true);

            if ($client === null) {
                throw new Exception('Broadcast getConnection return null');
            }

            // Initialize a new pubSubLoop consumer.
            $pubSubLoop = $client->pubSubLoop();

            if ($pubSubLoop === null) {
                throw new Exception('Broadcast pubSubLoop is null');
            }

            $channels = [];
            foreach (Broadcast::channels() as $key => $channel) {
                $channels[$key] = $channel . '.io';
            }

            // Subscribe to your channels
            $pubSubLoop->subscribe(ArrayHelper::merge(['control_channel'], $channels));

            // Start processing the $pubSubLoop messages. Open a terminal and use redis-cli
            // to push messages to the channels. Examples:
            //   ./redis-cli PUBLISH notifications "this is a test"
            //   ./redis-cli PUBLISH control_channel quit_loop
            foreach ($pubSubLoop as $message) {
                switch ($message->kind) {
                    case 'subscribe':
                        $this->output("Subscribed to {$message->channel}\n");
                        break;
                    case 'message':
                        if ($message->channel === 'control_channel') {
                            if ($message->payload === 'quit_loop') {
                                $this->output("Aborting pubSubLoop loop...\n", Console::FG_RED);
                                $pubSubLoop->unsubscribe();
                            } else {
                                $this->output("Received an unrecognized command: {$message->payload}\n", Console::FG_RED);
                            }
                        } else {
                            $payload = Json::decode($message->payload);
                            $data = $payload['data'] ?? [];

                            Broadcast::on($payload['name'], $data);
                            // Received the following message from {$message->channel}:") {$message->payload}";
                        }

                        break;
                }
            }

            // Always unset the pubSubLoop consumer instance when you are done! The
            // class destructor will take care of cleanups and prevent protocol
            // desynchronization between the client and the server.
            unset($pubSubLoop);
        };

        // Auto reconnect on redis timeout
        try {
            $pubSubLoop();
        } catch (ConnectionException) {
            $pubSubLoop();
        }

        return true;
    }
}
