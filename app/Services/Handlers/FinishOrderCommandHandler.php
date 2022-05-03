<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Models\Order;
use App\Services\Repositories\OrdersRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;

class FinishOrderCommandHandler implements HandlerInterface
{
    public function __construct(
        private OrdersRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $orderId = trim($message->getText());

        if (!$orderId || !$order = $this->repository->getById($orderId)) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                'Error: order id in invalid or missing'
            );
        }

        if ($order->isCompleted()) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                'Order has already been completed'
            );
        }

        $this->repository->finish($order);
        $this->dispatcher->dispatch('order.finished_by_command', ['order_id' => $orderId]);

        return new ProcessingResult(ProcessingResult::RESULT_OK, sprintf('Order %s has been finished', $orderId));
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_FINISH_ORDER;
    }
}
