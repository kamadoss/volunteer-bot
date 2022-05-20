<?php

declare(strict_types=1);

namespace Tests\Unit\Handlers;

use App\Models\Message;
use App\Models\Order;
use App\Services\DTO\ProcessingResult;
use App\Services\Events\EventDispatcherInterface;
use App\Services\Handlers\CancelOrderCommandHandler;
use App\Services\Repositories\OrdersRepositoryInterface;
use PHPUnit\Framework\Assert;
use Tests\Unit\AbstractTestCase;

class CancelOrderCommandHandlerTest extends AbstractTestCase
{
    public function testNotResponsibleForRandomMessage(): void
    {
        $handler = new CancelOrderCommandHandler(
            $this->createMock(OrdersRepositoryInterface::class),
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
        $handler = new CancelOrderCommandHandler(
            $this->createMock(OrdersRepositoryInterface::class),
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
        $handler = new CancelOrderCommandHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: 'Any text',
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    public function testResponsibleForChildCommand(): void
    {
        $handler = new CancelOrderCommandHandler(
            $this->createMock(OrdersRepositoryInterface::class),
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
            text: 'Any text',
            parentMessage: $randomMsg,
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        Assert::assertTrue($handler->isResponsible($cmd));
    }

    public function testHandleEmptyOrderId(): void
    {
        $handler = new CancelOrderCommandHandler(
            $this->createMock(OrdersRepositoryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: '',
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_ERROR);
        Assert::assertSame($result->getTextToAnswer(), 'Error: order id in invalid or missing');
    }

    public function testHandleWrongOrderId(): void
    {
        $orderId = 'wrong_order_id';

        $repo = $this->createMock(OrdersRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($orderId))
            ->willReturn(null);

        $handler = new CancelOrderCommandHandler(
            $repo,
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: $orderId,
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_ERROR);
        Assert::assertSame($result->getTextToAnswer(), 'Error: order id in invalid or missing');
    }

    /**
     * @dataProvider completedOrderStatuses
     */
    public function testHandleCompletedOrder(string $orderStatus): void
    {
        $orderId = 'some_order_id';

        $order = new Order(
            id: $orderId,
            messageSource: 'viber',
            messageId: 'order_msg_id',
            phoneNumber: '48111111111',
            orderText: 'Some order text',
            status: $orderStatus,
        );

        $repo = $this->createMock(OrdersRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($orderId))
            ->willReturn($order);

        $handler = new CancelOrderCommandHandler(
            $repo,
            $this->createMock(EventDispatcherInterface::class),
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: $orderId,
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_ERROR);
        Assert::assertSame($result->getTextToAnswer(), 'Order has already been completed');
    }

    /**
     * @dataProvider notFinishedOrderStatuses
     */
    public function testHandleOrder(string $orderStatus): void
    {
        $orderId = 'some_order_id';

        $order = new Order(
            id: $orderId,
            messageSource: 'viber',
            messageId: 'order_msg_id',
            phoneNumber: '48111111111',
            orderText: 'Some order text',
            status: $orderStatus,
        );

        $repo = $this->createMock(OrdersRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($orderId))
            ->willReturn($order);

        $repo->expects($this->once())
            ->method('cancel')
            ->with($this->equalTo($order));

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('order.cancelled_by_command'), $this->equalTo(['order_id' => $orderId]));

        $handler = new CancelOrderCommandHandler(
            $repo,
            $dispatcher,
        );

        $cmd = new Message(
            id: 'msg_id',
            source: 'viber',
            text: $orderId,
            commandName: Message::COMMAND_CANCEL_ORDER,
        );

        $result = $handler->handle($cmd);

        Assert::assertInstanceOf(ProcessingResult::class, $result);
        Assert::assertSame($result->getResultCode(), ProcessingResult::RESULT_OK);
        Assert::assertSame($result->getTextToAnswer(), sprintf('Order %s has been cancelled', $orderId));
    }

    public function completedOrderStatuses(): array
    {
        return [
            [
                Order::STATUS_CANCELLED,
            ],
            [
                Order::STATUS_FINISHED,
            ],
        ];
    }

    public function notFinishedOrderStatuses(): array
    {
        return [
            [
                Order::STATUS_NEW,
            ],
            [
                Order::STATUS_PROCESSING,
            ],
        ];
    }
}
