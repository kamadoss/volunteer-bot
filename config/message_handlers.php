<?php

declare(strict_types=1);

use App\Services\Handlers;

return [
    'handlers' => [
        // order matters
        Handlers\BlackListCommandHandler::class,
        Handlers\OrdersListCommandHandler::class,
        Handlers\CancelOrderCommandHandler::class,
        Handlers\FinishOrderCommandHandler::class,
        Handlers\AddToBlackListCommandHandler::class,
        Handlers\NewOrderPlainTextHandler::class,
        Handlers\CancelOrderPlainTextHandler::class,
        Handlers\FinishOrderPlainTextHandler::class,
    ],
    'default_handler' => Handlers\IgnoreMessageHandler::class,
    'cancel_order_keywords' => [
        'cancelled',
        'canceled',
        'cancel',
        'closed',
        'close',
        'removed',
        'remove',
        'deleted',
        'delete',
    ],
    'finish_order_keywords' => [
        'finished',
        'finish',
        'complete',
        'completed',
        'done',
        'ready',
        'processed',
    ],
];
