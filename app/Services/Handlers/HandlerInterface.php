<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\DTO\ProcessingResult;

interface HandlerInterface
{
    public function handle(Message $message): ProcessingResult;

    public function isResponsible(Message $message): bool;
}
