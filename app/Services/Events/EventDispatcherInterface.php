<?php

declare(strict_types=1);

namespace App\Services\Events;

interface EventDispatcherInterface
{
    // todo move to event classes
    public function dispatch(string $key, array $payload);
}
