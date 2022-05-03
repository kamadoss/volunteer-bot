<?php

declare(strict_types=1);

use App\Console\Application;
use App\Services\Handlers\HandlerInitializer;
use App\Services\Handlers\HandlerInitializerInterface;
use App\Services\Helpers\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(Command::class)
        ->tag('command');

    $services->load('App\\', '../app/{Console,Services}');

    $services->set(Application::class, Application::class)
        ->public()
        ->args([tagged_iterator('command')]);

    $services->set(HandlerInitializerInterface::class, HandlerInitializer::class);

    $publicServices = [
        ...Config::get('message_handlers.handlers'),
        Config::get('message_handlers.default_handler')
    ];

    foreach ($publicServices as $oneService) {
        $services->set($oneService)
            ->public();
    }
};
