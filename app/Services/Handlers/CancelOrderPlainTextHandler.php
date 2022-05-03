<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Repositories\OrdersRepositoryInterface;
use App\Services\Helpers\Config;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;

class CancelOrderPlainTextHandler extends AbstractKeyWordReplyHandler
{
    public function __construct(
        private OrdersRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $originalMessage = $message->getParentMessage();
        $order = $this->repository->findByMessage($originalMessage->getId(), $originalMessage->getSource());

        if ($order->isCompleted()) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                'Order has already been completed'
            );
        }

        $this->repository->cancel($order);
        $this->dispatcher->dispatch('order.cancelled_by_message', ['order_id' => $order->getId()]);

        return new ProcessingResult(
            ProcessingResult::RESULT_OK,
            sprintf('Order %s has been cancelled', $order->getId())
        );
    }

    /**
     * @inheritDoc
     */
    protected function getKeyWords(): array
    {
        return Config::get('message_handlers.cancel_order_keywords');
    }
}
