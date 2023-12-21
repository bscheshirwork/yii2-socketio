<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventInterface
 * Event name and broadcast nsp
 */
interface EventInterface
{
    /**
     * List broadcast nsp array
     */
    public static function broadcastOn(): array;

    /**
     * Get event name
     */
    public static function name(): string;
}
