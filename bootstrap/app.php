<?php

declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

define('BASE_PATH', realpath(__DIR__ . '/..'));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/bootstrap/services.php';

$containerBuilder = new ContainerBuilder();
$loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('services.php');

$containerBuilder->compile();
