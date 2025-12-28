<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Service\Qiq\Helper;

use Qiq\Helper\Html\Escape;

class Foo
{
    public function __construct(protected Escape $escape)
    {
    }

    public function __invoke(string $text): string
    {
        return $this->escape->h('Foo:' . $text);
    }
}
