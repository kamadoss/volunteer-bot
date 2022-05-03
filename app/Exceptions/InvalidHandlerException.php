<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\Handlers\HandlerInterface;

class InvalidHandlerException extends \Exception
{
    public function __construct(string $handlerClass)
    {
        $msg = sprintf('Message handler "%s" is not an instance of "%s"', $handlerClass, HandlerInterface::class);

        parent::__construct($msg);
    }
}
