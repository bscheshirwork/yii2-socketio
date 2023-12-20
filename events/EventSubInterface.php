<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventSubInterface
 * Event subscriber interface
 *
 * @package bscheshirwork\socketio\events
 */
interface EventSubInterface
{
    /**
     * Handle published event data
     *
     * @param array $data
     *
     * @return mixed
     */
    public function handle(array $data);
}
