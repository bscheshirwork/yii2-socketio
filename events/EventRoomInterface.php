<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventRoomInterface
 * Provide room support for event
 *
 * @package bscheshirwork\socketio\events
 */
interface EventRoomInterface
{
    /**
     * Get room name
     *
     * @return string
     */
    public function room(): string;
}
