<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Services\Helpers\Config;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HandlerInitializer implements HandlerInitializerInterface, ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $locator)
    {
    }

    public function initializeByClass(string $className): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = $this->locator->get($className);

        return $handler;
    }

    public static function getSubscribedServices(): array
    {
        return Config::get('message_handlers.handlers');
    }
}
