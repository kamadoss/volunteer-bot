<?php

declare(strict_types=1);

namespace App\Services\Helpers;

class Config
{
    private const CONFIG_PATH = BASE_PATH . '/config';

    public static function get(string $configPath): mixed
    {
        $splitPath = explode('.', $configPath, 2);
        $filePath = sprintf('%s/%s.php', self::CONFIG_PATH, $splitPath[0]);
        $fileConfig = require $filePath;

        if (count($splitPath) === 1) {
            return $fileConfig;
        }

        return self::getFromConfig($fileConfig, $splitPath[1]);
    }

    private static function getFromConfig(array $config, string $foldingPath): mixed
    {
        $splitPath = explode('.', $foldingPath, 2);
        $currentLevel = $splitPath[0];

        if (!isset($config[$currentLevel])) {
            return null;
        }

        return isset($splitPath[1])
            ? self::getFromConfig($config[$currentLevel], $splitPath[1])
            : $config[$currentLevel];
    }
}
