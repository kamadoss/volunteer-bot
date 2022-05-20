<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Repositories\OrdersRepositoryInterface;

class CancelOrderCommandHandler implements HandlerInterface
{
    public function __construct(
        private readonly OrdersRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
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

        $this->repository->cancel($order);
        $this->dispatcher->dispatch('order.cancelled_by_command', ['order_id' => $orderId]);

        return new ProcessingResult(ProcessingResult::RESULT_OK, sprintf('Order %s has been cancelled', $orderId));
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_CANCEL_ORDER;
    }
}
