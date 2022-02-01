<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Layout
{
    public function __construct(public string $value)
    {
    }
}
