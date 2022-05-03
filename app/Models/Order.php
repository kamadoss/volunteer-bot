<?php

declare(strict_types=1);

namespace App\Models;

class Order
{
    public const
        STATUS_NEW = 'new',
        STATUS_PROCESSING = 'processing',
        STATUS_CANCELLED = 'cancelled',
        STATUS_FINISHED = 'finished';

    public function __construct(
        private string $id,
        private string $messageSource,
        private string $messageId,
        private string $phoneNumber,
        private string $orderText,
        private string $status,
    ) {
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getOrderText(): string
    {
        return $this->orderText;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessageSource(): string
    {
        return $this->messageSource;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function isCompleted(): bool
    {
        return in_array($this->getStatus(), [Order::STATUS_FINISHED, Order::STATUS_CANCELLED], true);
    }
}
