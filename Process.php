<?php

namespace bscheshirwork\socketio;

use Yii;
use yii\helpers\HtmlPurifier;

final class Process
{
    public ?string $yiiAlias;

    private static array $_inWork = [];

    public function getParallelEnv(): int
    {
        return getenv('SOCKET_IO.PARALLEL') ?: 10;
    }

    /**
     * Run process. If more of limit then wait and try to run process on more time.
     * @throws \JsonException
     */
    public function run(string $handle, array $data): \Symfony\Component\Process\Process
    {
        $this->inWork();

        while (count(self::$_inWork) >= $this->getParallelEnv()) {
            usleep(100);

            $this->inWork();
        }

        return $this->push($handle, $data);
    }

    /**
     * In work processes
     */
    private function inWork(): void
    {
        foreach (self::$_inWork as $i => $process) {
            if ($process->isRunning() === false) {
                unset(self::$_inWork[$i]);
            }
        }
    }

    /**
     * Create cmd process and push to queue.
     * @throws \JsonException
     */
    private function push(string $handle, array $data): \Symfony\Component\Process\Process
    {
        $cmd = [
            'php',
            'yii',
            'socketio/process',
            HtmlPurifier::process(escapeshellarg($handle)),
            HtmlPurifier::process(escapeshellarg(json_encode($data, JSON_THROW_ON_ERROR))),
        ];

        if ($this->yiiAlias === null) {
            if (file_exists(Yii::getAlias('@app/yii'))) {
                $this->yiiAlias = '@app';
            } elseif (file_exists(Yii::getAlias('@app/../yii'))) {
                $this->yiiAlias = '@app/../';
            }
        }

        $process = new \Symfony\Component\Process\Process($cmd, Yii::getAlias($this->yiiAlias));
        $process->setTimeout(10);
        $process->start();

        self::$_inWork[] = $process;

        return $process;
    }
}
