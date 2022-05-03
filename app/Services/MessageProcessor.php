<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidHandlerException;
use App\Models\Message;
use App\Services\DTO\ProcessingResult;
use App\Services\Handlers\HandlerFactory;

class MessageProcessor
{
    public function __construct(private readonly HandlerFactory $handlerFactory)
    {
    }

    /**
     * @throws InvalidHandlerException
     */
    public function process(Message $message): ProcessingResult
    {
        return $this->handlerFactory
            ->createForMessage($message)
            ->handle($message);
    }
}
