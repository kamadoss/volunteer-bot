<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use App\Models\BlackList;
use App\Models\Message;
use App\Services\DTO\ProcessingResult;
use App\Services\Handlers\BlackListCommandHandler;
use App\Services\Repositories\BlackListRepositoryInterface;
use App\Services\Transformers\BlackListToStringTransformer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class BlackListCommandHandlerTest extends TestCase
{
    public function testNotResponsibleForRandomMessage(): void
    {
        $handler = new BlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            new BlackListToStringTransformer()
        );

        $msg = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random message',
        );

        Assert::assertFalse($handler->isResponsible($msg));
    }

    public function testNotResponsibleForOtherCommand(): void
    {
        $handler = new BlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            new BlackListToStringTransformer()
        );

        $msg = new Message(
            id: 'msg_id',
            source: 'telg',
            text: 'Some random number +48 768 78 78',
            commandName: 'some_command'
        );

        Assert::assertFalse($handler->isResponsible($msg));
    }

    public function testResponsibleForCommand(): void
    {
        $handler = new BlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            new BlackListToStringTransformer()
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: "Any text",
            commandName: Message::COMMAND_BLACKLIST,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    public function testResponsibleForChildCommand(): void
    {
        $handler = new BlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            new BlackListToStringTransformer()
        );

        $randomMsg = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random number +48 768 78 78',
        );

        $cmd = new Message(
            id: 'msg_id_2',
            source: 'facebook',
            text: "Any text",
            parentMessage: $randomMsg,
            commandName: Message::COMMAND_BLACKLIST,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    public function testHandle(): void
    {
        $phoneNumbers = [
            '48234234234',
            '789789789',
            '390501233445',
        ];

        $blackList = new BlackList($phoneNumbers);

        $repo = $this->createMock(BlackListRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getAll')
            ->willReturn($blackList);

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: "Any text",
            commandName: Message::COMMAND_BLACKLIST,
        );

        $handler = new BlackListCommandHandler($repo, new BlackListToStringTransformer());
        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame(ProcessingResult::RESULT_OK, $result->getResultCode());
        Assert::assertSame(implode(PHP_EOL, $phoneNumbers), $result->getTextToAnswer());
    }
}
