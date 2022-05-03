<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\DTO\ProcessingResult;

class IgnoreMessageHandler implements HandlerInterface
{
    public function handle(Message $message): ProcessingResult
    {
        return new ProcessingResult();
    }

    public function isResponsible(Message $message): bool
    {
        return true;
    }
}
