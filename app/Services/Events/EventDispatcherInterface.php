<?php

declare(strict_types=1);

namespace App\Services\Events;

interface EventDispatcherInterface
{
    public function dispatch(string $key, array $payload);
}
