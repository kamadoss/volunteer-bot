<?php

declare(strict_types=1);

namespace App\Models;

class Message
{
    public const
        COMMAND_BLACKLIST = 'blacklist',
        COMMAND_CANCEL_ORDER = 'cancel_order',
        COMMAND_FINISH_ORDER = 'finish_order',
        COMMAND_LIST_ACTIVE_ORDERS = 'active_orders',
        COMMAND_ADD_TO_BLACKLIST = 'add_to_blacklist';

    public function __construct(
        private string $id,
        private string $source,
        private string $text,
        private ?Message $parentMessage = null,
        private ?string $commandName = null,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getParentMessage(): ?Message
    {
        return $this->parentMessage;
    }

    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    public function isCommand(): bool
    {
        return $this->getCommandName() !== null;
    }

    public function isReply(): bool
    {
        return $this->getParentMessage() !== null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
