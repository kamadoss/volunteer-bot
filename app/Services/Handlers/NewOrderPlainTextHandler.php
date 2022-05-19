<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Factories\OrderFactoryInterface;
use App\Services\Helpers\Phone;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\Repositories\OrdersRepositoryInterface;

class NewOrderPlainTextHandler implements HandlerInterface
{
    public function __construct(
        private readonly BlackListRepositoryInterface $blackListRepository,
        private readonly OrdersRepositoryInterface $ordersRepository,
        private readonly OrderFactoryInterface $orderFactory,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $phone = Phone::getFirstFromText($message->getText());

        if ($this->blackListRepository->existsInBlackList($phone)) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                sprintf('Phone number %s is in the blacklist', $phone)
            );
        }

        $existingOrders = $this->ordersRepository->findActiveByNumber($phone);
        $hasActiveOrders = count($existingOrders) > 0;
        $order = $this->orderFactory->createFromMessage($message, $phone);

        $this->ordersRepository->save($order);
        $this->dispatcher->dispatch('order.created_from_message', ['order_id' => $order->getId()]);

        return $hasActiveOrders
            ? new ProcessingResult(
                ProcessingResult::RESULT_WARNING,
                sprintf('Order created. There are more active orders for number %s', $phone)
            )
            : new ProcessingResult(ProcessingResult::RESULT_OK, 'Order created');
    }

    public function isResponsible(Message $message): bool
    {
        $firstPhone = Phone::getFirstFromText($message->getText());

        return !$message->isCommand() && !$message->isReply() && $firstPhone !== null;
    }
}
