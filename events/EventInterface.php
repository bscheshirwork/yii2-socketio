<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventInterface
 * Event name and broadcast nsp
 *
 * @package bscheshirwork\socketio\events
 */
interface EventInterface
{
    /**
     * List broadcast nsp array
     *
     * @return array
     */
    public static function broadcastOn(): array;

    /**
     * Get event name
     *
     * @return string
     */
    public static function name(): string;
}
