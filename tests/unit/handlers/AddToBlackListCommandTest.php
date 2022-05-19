<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use App\Models\Message;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Handlers\AddToBlackListCommandHandler;
use App\Services\Repositories\BlackListRepositoryInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class AddToBlackListCommandTest extends TestCase
{
    public function testNotResponsibleForRandomMessage(): void
    {
        $handler = new AddToBlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
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
        $handler = new AddToBlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
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
        $handler = new AddToBlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: "Any text",
            commandName: Message::COMMAND_ADD_TO_BLACKLIST,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    public function testResponsibleForChildCommand(): void
    {
        $handler = new AddToBlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
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
            commandName: Message::COMMAND_ADD_TO_BLACKLIST,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    /**
     * @dataProvider wrongFormatTextProvider
     */
    public function testHandlerWrongFormat(string $messageText): void
    {
        $handler = new AddToBlackListCommandHandler(
            $this->createMock(BlackListRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: $messageText,
            commandName: Message::COMMAND_ADD_TO_BLACKLIST,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_ERROR);
        Assert::assertSame($result->getTextToAnswer(), 'Error: phone is missing or has an invalid format');
    }

    /**
     * @dataProvider suitedTextProvider
     */
    public function testHandleSuccess(string $messageText, array $phoneNumbers, string $response): void
    {
        $repo = $this->createMock(BlackListRepositoryInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $expectedArguments = array_map(fn (string $phone) => [$this->equalTo($phone)], $phoneNumbers);

        $repo->expects($this->exactly(count($phoneNumbers)))
            ->method('putToBlackList')
            ->withConsecutive(...$expectedArguments);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('phone.added_to_blacklist_by_command'), $this->equalTo(['phones' => $phoneNumbers]));

        $handler = new AddToBlackListCommandHandler($repo, $dispatcher);

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: $messageText,
            commandName: Message::COMMAND_ADD_TO_BLACKLIST,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_OK);
        Assert::assertSame($result->getTextToAnswer(), $response);
    }

    public function wrongFormatTextProvider(): array
    {
        return [
            // no phone
            [
                'Wrong message format',
            ],
            // only spaces
            [
                '   ',
            ],
            // empty message
            [
                '',
            ],
        ];
    }

    public function suitedTextProvider(): array
    {
        return [
            // a single number with spaces and braces
            [
                '+123 (45) 6-789',
                [
                    '123456789',
                ],
                'The following phone number has been added to the blacklist: 123456789',
            ],
            // single number normalized
            [
                '+012345678912345',
                [
                    '012345678912345',
                ],
                'The following phone number has been added to the blacklist: 012345678912345',
            ],
            // multiple phones in different formats
            [
                'Please add these phones to the back list: +48 767 777 444 and +390 (50) 111-11-11',
                [
                    '48767777444',
                    '390501111111',
                ],
                'The following phone numbers have been added to the blacklist: 48767777444, 390501111111',
            ],
            // multiple not-unique comma-separated phones
            [
                '+48 767 777 444,+390 (50) 111-11-11, 48123123123,48123123123',
                [
                    '48767777444',
                    '390501111111',
                    '48123123123',
                ],
                'The following phone numbers have been added to the blacklist: 48767777444, 390501111111, 48123123123',
            ],
        ];
    }
}
