<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Module;

use BEAR\Package\AbstractAppModule;

use BEAR\QiqModule\QiqModule;
use function dirname;

class HtmlModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new QiqModule(dirname(__DIR__, 2) . '/var/qiq/template'));
    }
}
