<?php

declare(strict_types=1);

namespace App\Services\Factories;

use App\Models\Message;
use App\Models\Order;

interface OrderFactoryInterface
{
    public function createFromMessage(Message $message, string $phoneNumber): Order;
}
