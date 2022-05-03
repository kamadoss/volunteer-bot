<?php

declare(strict_types=1);

namespace App\Models;

class BlackList
{
    /**
     * @param string[] $blackListedNumbers
     */
    public function __construct(private array $blackListedNumbers)
    {
    }

    public function getBlackListedNumbers(): array
    {
        return $this->blackListedNumbers;
    }
}
