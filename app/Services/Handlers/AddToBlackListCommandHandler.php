<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Helpers\Phone;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;

class AddToBlackListCommandHandler implements HandlerInterface
{
    public function __construct(
        private readonly BlackListRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $phones = Phone::getAllNormalizedFromText($message->getText());

        if (empty($phones)) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                'Error: phone is missing or has an invalid format'
            );
        }

        foreach ($phones as $onePhone) {
            $this->repository->putToBlackList($onePhone);
        }

        $this->dispatcher->dispatch('phone.added_to_blacklist_by_command', ['phones' => $phones]);

        return new ProcessingResult(
            ProcessingResult::RESULT_OK,
            $this->getSuccessTextForPhones($phones)
        );
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_ADD_TO_BLACKLIST;
    }

    private function getSuccessTextForPhones(array $phones): string
    {
        $isSinglePhone = count($phones) === 1;

        return sprintf(
            'The following phone %s %s been added to the blacklist: %s',
            $isSinglePhone ? 'number' : 'numbers',
            $isSinglePhone ? 'has' : 'have',
            implode(', ', $phones)
        );
    }
}
