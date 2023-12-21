<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventSubInterface
 * Event subscriber interface
 */
interface EventSubInterface
{
    /**
     * Handle published event data
     *
     * @return mixed
     */
    public function handle(array $data);
}
