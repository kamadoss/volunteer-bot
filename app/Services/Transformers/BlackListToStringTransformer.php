<?php

declare(strict_types=1);

namespace App\Services\Transformers;

use App\Models\BlackList;

class BlackListToStringTransformer
{
    public function transform(BlackList $blackList): string
    {
        return implode("\n", $blackList->getBlackListedNumbers());
    }
}
