<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventRoomInterface
 * Provide room support for event
 */
interface EventRoomInterface
{
    /**
     * Get room name
     */
    public function room(): string;
}
