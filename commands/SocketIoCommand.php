<?php

namespace bscheshirwork\socketio\commands;

use Exception;
use Symfony\Component\Process\Process;
use yiicod\cron\commands\DaemonController;

/**
 * Class SocketIoCommand
 * Run this daemon for listen socketio. Don't forget about run npm install in the folder "server".
 */
final class SocketIoCommand extends DaemonController
{
    use CommandTrait;

    /**
     * Daemon name
     */
    protected function daemonName(): string
    {
        return 'socket.io';
    }

    /**
     * SocketOI worker
     * @throws Exception
     */
    protected function worker(): void
    {
        $process = $this->nodejs();
        $process->disableOutput();
        $process->start();

        // Save node process pid
        $this->addPid($process->getPid());

        while ($process->isRunning()) {
            $this->predis();
        }
    }
}
