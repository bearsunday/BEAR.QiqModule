<?php

declare(strict_types=1);

namespace MyVendor\MyProject\Service\Qiq\Helper;

use Qiq\Helper\Html\HtmlHelpers;

class Helpers extends HtmlHelpers
{
    public function foo(string $text): string
    {
        return $this->get(Foo::class)->__invoke($text);
    }

    public function bar(string $text): string
    {
        return $this->get(Bar::class)->__invoke($text);
    }
}
