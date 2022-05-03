<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\Models\BlackList;

interface BlackListRepositoryInterface
{
    public function getAll(): BlackList;

    public function putToBlackList(string $phoneNumber): void;

    public function existsInBlackList(string $phoneNumber): bool;
}
