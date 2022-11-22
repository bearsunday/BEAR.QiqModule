<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<\Qiq\HelperLocator> */
class HelperLocatorProvider implements ProviderInterface
{
    public function get(): \Qiq\HelperLocator
    {
        return HelperLocator::new();
    }
}
