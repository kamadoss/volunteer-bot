<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Repositories\OrdersRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Transformers\OrderListToStringTransformer;

class OrdersListCommandHandler implements HandlerInterface
{
    public function __construct(
        private OrdersRepositoryInterface $repository,
        private OrderListToStringTransformer $transformer
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $orders = $this->repository->getActive();
        $transformed = $this->transformer->transform($orders);

        return new ProcessingResult(ProcessingResult::RESULT_OK, $transformed);
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_LIST_ACTIVE_ORDERS;
    }
}
