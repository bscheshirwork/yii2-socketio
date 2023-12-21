<?php

namespace bscheshirwork\socketio\events;

/**
 * Interface EventPubInterface
 * Event publish interface
 */
interface EventPubInterface
{
    /**
     * Process event and return result to subscribers
     */
    public function fire(array $data): array;
}
