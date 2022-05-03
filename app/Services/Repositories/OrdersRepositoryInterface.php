<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\Models\Order;

interface OrdersRepositoryInterface
{
    /**
     * @return Order[]
     */
    public function getActive(): array;

    public function getById(string $orderId): ?Order;

    public function cancel(Order $order): void;

    public function finish(Order $order): void;

    /**
     * @return Order[]
     */
    public function findActiveByNumber(string $phoneNumber): array;

    public function findByMessage(string $messageId, string $messageSource): ?Order;

    public function save(Order $order): void;
}
