<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use App\Models\Message;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Handlers\CancelOrderPlainTextHandler;
use App\Services\Repositories\OrdersRepositoryInterface;
use PHPUnit\Framework\Assert;
use Tests\Unit\AbstractTestCase;

class CancelOrderPlainTextHandlerTest extends AbstractTestCase
{
    /**
     * @dataProvider keywordText
     */
    public function testNotResponsibleForParentMessage(string $keywordText): void
    {
        $handler = new CancelOrderPlainTextHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $msg = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: $keywordText,
        );

        Assert::assertFalse($handler->isResponsible($msg));
    }

    /**
     * @dataProvider keywordText
     */
    public function testNotResponsibleForChildCommands(string $keywordText): void
    {
        $handler = new CancelOrderPlainTextHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $msgOne = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random message',
        );

        $msgTwo = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: $keywordText,
            parentMessage: $msgOne,
            commandName: 'some_command',
        );

        Assert::assertFalse($handler->isResponsible($msgTwo));
    }

    public function testNotResponsibleForRandomChildMessage(): void
    {
        $handler = new CancelOrderPlainTextHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $msgOne = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random message',
        );

        $msgTwo = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random reply',
            parentMessage: $msgOne,
        );

        Assert::assertFalse($handler->isResponsible($msgTwo));
    }

    /**
     * @dataProvider keywordText
     */
    public function testResponsibleForChildKeywordMessage(string $keywordText): void
    {
        $handler = new CancelOrderPlainTextHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $msgOne = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: 'Some random message',
        );

        $msgTwo = new Message(
            id: 'msg_id_1',
            source: 'telg',
            text: $keywordText,
            parentMessage: $msgOne,
        );

        Assert::assertTrue($handler->isResponsible($msgTwo));
    }

    public function keywordText(): array
    {
        return [
            [
                'cancelled order',
            ],
            [
                'canceled',
            ],
            [
                'cancel order',
            ],
            [
                'closed',
            ],
            [
                'order close',
            ],
            [
                'removed',
            ],
            [
                'remove this order',
            ],
            [
                'deleted',
            ],
            [
                'order to delete',
            ],
        ];
    }
}
