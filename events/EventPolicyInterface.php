<?php

namespace bscheshirwork\socketio\events;

interface EventPolicyInterface
{
    public function can($data): bool;
}
