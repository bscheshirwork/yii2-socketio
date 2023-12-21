<?php

namespace bscheshirwork\socketio\commands;

use Exception;
use yii\console\Controller;

/**
 * Socketio server. You should run two commands: "socketio/node-js-server" and "socketio/php-server". Use pm2 as daemon manager.
 */
final class WorkerCommand extends Controller
{
    use CommandTrait;

    /**
     * @var string
     */
    public $defaultAction = 'work';

    public int $delay = 15;

    /**
     * Node js listener.
     *
     * @throws Exception
     */
    public function actionNodeJsServer(): void
    {
        $process = $this->nodejs();
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->run();
    }

    /**
     * Php listener
     *
     * @throws Exception
     */
    public function actionPhpServer(): void
    {
        while (true) {
            $this->predis();
        }
    }

    private function output($text): void
    {
        $this->stdout($text);
    }
}
