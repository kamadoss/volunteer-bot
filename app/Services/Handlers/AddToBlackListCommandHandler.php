<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;

class AddToBlackListCommandHandler implements HandlerInterface
{
    private const PHONE_REGEX = '/^\+?\d{9,15}$/';

    public function __construct(
        private BlackListRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Message $message): ProcessingResult
    {
        $phone = trim($message->getText());

        if (!preg_match(self::PHONE_REGEX, $phone)) {
            return new ProcessingResult(
                ProcessingResult::RESULT_ERROR,
                'Error: phone is missing or has an invalid format'
            );
        }

        $this->repository->putToBlackList($phone);
        $this->dispatcher->dispatch('phone.added_to_blacklist_by_command', ['phone' => $phone]);

        return new ProcessingResult(
            ProcessingResult::RESULT_OK,
            sprintf('Phone number %s has been added to the blacklist', $phone)
        );
    }

    public function isResponsible(Message $message): bool
    {
        return $message->isCommand() && $message->getCommandName() === Message::COMMAND_ADD_TO_BLACKLIST;
    }
}
