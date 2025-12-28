<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Service\Qiq\Helper;

use Qiq\Helper\Html\Escape;

class Bar
{
    public function __construct(protected Escape $escape)
    {
    }

    public function __invoke(string $text): string
    {
        return $this->escape->h('Bar:' . $text);
    }
}
