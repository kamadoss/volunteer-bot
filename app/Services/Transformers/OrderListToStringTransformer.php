<?php

declare(strict_types=1);

namespace App\Services\Transformers;

use App\Models\Order;

class OrderListToStringTransformer
{
    /**
     * @param Order[] $orders
     */
    public function transform(array $orders): string
    {
        $orderLines = [];

        foreach ($orders as $oneOrder) {
            $orderLines[] = sprintf('Phone: %s, ID: %s', $oneOrder->getPhoneNumber(), $oneOrder->getId());
        }

        return implode("\n", $orderLines);
    }
}
