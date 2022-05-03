<?php

declare(strict_types=1);

namespace App\Services\DTO;

class ProcessingResult
{
    public const
        RESULT_OK = 'ok',
        RESULT_WARNING = 'warning',
        RESULT_ERROR = 'error';

    public function __construct(private string $resultCode = self::RESULT_OK, private ?string $textToAnswer = null)
    {
    }

    public function getTextToAnswer(): ?string
    {
        return $this->textToAnswer;
    }

    public function getResultCode(): string
    {
        return $this->resultCode;
    }
}
