<?php

namespace bscheshirwork\socketio;

use bscheshirwork\socketio\drivers\RedisDriver;
use bscheshirwork\socketio\events\EventPolicyInterface;
use bscheshirwork\socketio\events\EventPubInterface;
use bscheshirwork\socketio\events\EventRoomInterface;
use bscheshirwork\socketio\events\EventSubInterface;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use yiicod\base\helpers\LoggerMessage;

final class Broadcast
{
    private static array $channels = [];

    /**
     * Subscribe to event from client
     *
     * @throws Exception
     */
    public static function on(string $event, array $data): void
    {
        // Clear data
        array_walk_recursive($data, static function (&$item, $key): void {
            $item = HtmlPurifier::process($item);
        });

        Yii::info(Json::encode([
            'type' => 'on',
            'name' => $event,
            'data' => $data,
        ]), 'socket.io');

        $eventClassName = self::getManager()->getList()[$event] ?? null;
        if ($eventClassName === null) {
            Yii::error(LoggerMessage::trace("Can not find {$event}", $data));
        }

        Yii::$container->get(Process::class)->run($eventClassName, $data);
    }

    /**
     * Handle process from client
     */
    public static function process(string $handler, array $data): void
    {
        try {
            /** @var EventSubInterface|EventPolicyInterface $event */
            $event = new $handler($data);

            if ($event instanceof EventSubInterface === false) {
                throw new Exception('Event should implement EventSubInterface');
            }

            Yii::$app->db->close();
            Yii::$app->db->open();

            if ($event instanceof EventPolicyInterface && $event->can($data) === false) {
                return;
            }

            $event->handle($data);
        } catch (Exception $exception) {
            Yii::error(LoggerMessage::log($exception, Json::encode($data)));
        }
    }

    /**
     * Emit event to client
     *
     * @throws Exception
     */
    public static function emit(string $event, array $data): void
    {
        $eventClassName = self::getManager()->getList()[$event] ?? null;
        try {
            if ($eventClassName === null) {
                throw new Exception("Can not find {$event}");
            }

            /** @var EventPubInterface|EventRoomInterface $event */
            $event = new $eventClassName($data);

            if ($event instanceof EventPubInterface === false) {
                throw new Exception('Event should implement EventPubInterface');
            }

            $data = $event->fire($data);

            if ($event instanceof EventRoomInterface) {
                $data['room'] = $event->room();
            }

            Yii::info(Json::encode([
                'type' => 'emit',
                'name' => $event,
                'data' => $data,
            ]), 'socket.io');
            foreach ($eventClassName::broadcastOn() as $channel) {
                self::publish(self::channelName($channel), [
                    'name' => $eventClassName::name(),
                    'data' => $data,
                ]);
            }
        } catch (Exception $exception) {
            Yii::error(LoggerMessage::log($exception));
        }
    }

    /**
     * Prepare channel name
     */
    public static function channelName(string $name): string
    {
        return $name . self::getManager()->nsp;
    }

    /**
     * Publish data to redis channel
     */
    public static function publish(string $channel, array $data): void
    {
        self::getDriver()->getConnection(true)->publish($channel, Json::encode($data));
    }

    /**
     * Redis channels names
     */
    public static function channels(): array
    {
        if (empty(self::$channels)) {
            foreach (self::getManager()->getList() as $eventClassName) {
                self::$channels = ArrayHelper::merge(self::$channels, $eventClassName::broadcastOn());
            }

            self::$channels = array_unique(self::$channels);

            self::$channels = array_map(static fn ($channel): string => self::channelName($channel), self::$channels);
            //Yii::info(Json::encode(self::$channels));
        }

        return self::$channels;
    }

    /**
     * @return RedisDriver
     */
    public static function getDriver()
    {
        return Yii::$app->broadcastDriver;
    }

    /**
     * @return EventManager
     */
    public static function getManager()
    {
        return Yii::$app->broadcastEvents;
    }
}
