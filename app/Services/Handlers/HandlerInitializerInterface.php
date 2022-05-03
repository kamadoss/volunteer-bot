<?php

declare(strict_types=1);

namespace App\Services\Handlers;

interface HandlerInitializerInterface
{
    public function initializeByClass(string $className): HandlerInterface;
}
