<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\Repositories\OrdersRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Factories\OrderFactoryInterface;

class NewOrderPlainTextHandler implements HandlerInterface
{
    private const PHONE_REGEX = '/\+?\d[\d\s()\-]{7,20}\d/';

    public function __construct(
        private BlackListRepositoryInterface $blackListRepository,
        private OrdersRepositoryInterface $ordersRepository,
        private OrderFactoryInterface $orderFactory,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $phone = $this->getPhoneFromMessage($message);

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
        return !$message->isCommand() && !$message->isReply() && $this->getPhoneFromMessage($message) !== null;
    }

    private function getPhoneFromMessage(Message $message): ?string
    {
        $text = trim($message->getText());
        $matches = [];

        preg_match(self::PHONE_REGEX, $text, $matches);
        $cleanedUp = preg_replace('/[^\d\+]]/', '', !empty($matches[0]) ? $matches[0] : '');

        return $cleanedUp ?: null;
    }
}
