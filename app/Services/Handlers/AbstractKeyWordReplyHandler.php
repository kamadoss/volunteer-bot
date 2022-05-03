<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Models\Message;
use App\Services\Helpers\Config;
use App\Services\DTO\ProcessingResult;

abstract class AbstractKeyWordReplyHandler implements HandlerInterface
{
    public function isResponsible(Message $message): bool
    {
        return !$message->isCommand() && $message->isReply() && $this->hasKeyWord($message);
    }

    /**
     * @return string[]
     */
    abstract protected function getKeyWords(): array;

    private function hasKeyWord(Message $message): bool
    {
        $text = mb_strtolower(trim($message->getText()));
        $splitText = explode(' ', $text);

        return count(array_intersect($this->getKeyWords(), $splitText)) > 0;
    }
}
