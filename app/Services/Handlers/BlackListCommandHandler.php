<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Transformers\BlackListToStringTransformer;

class BlackListCommandHandler implements HandlerInterface
{
    public function __construct(
        private readonly BlackListRepositoryInterface $blackListRepository,
        private readonly BlackListToStringTransformer $transformer
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $blacklist = $this->blackListRepository->getAll();
        $transformed = $this->transformer->transform($blacklist);

        return new ProcessingResult(ProcessingResult::RESULT_OK, $transformed);
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_BLACKLIST;
    }
}
