<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Exceptions\InvalidHandlerException;
use App\Models\Message;
use App\Services\Helpers\Config;

class HandlerFactory
{
    public function __construct(private readonly HandlerInitializerInterface $handlerInitializer)
    {
    }

    /**
     * @throws InvalidHandlerException
     */
    public function createForMessage(Message $message): HandlerInterface
    {
        $handlerClasses = Config::get('message_handlers.handlers');

        foreach ($handlerClasses as $oneClass) {
            $handler = $this->getHandler($oneClass);

            if ($handler->isResponsible($message)) {
                return $handler;
            }
        }

        return $this->getHandler(Config::get('message_handlers.default_handler'));
    }

    /**
     * @throws InvalidHandlerException
     */
    private function getHandler(string $className): HandlerInterface
    {
        if (!is_subclass_of($className, HandlerInterface::class)) {
            throw new InvalidHandlerException($className);
        }

        return $this->handlerInitializer->initializeByClass($className);
    }
}
