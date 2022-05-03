<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct(iterable $commands = null)
    {
        parent::__construct();

        if ($commands === null) {
            return;
        }

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
