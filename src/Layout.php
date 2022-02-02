<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Layout
{
    public function __construct(public string $value)
    {
    }
}
