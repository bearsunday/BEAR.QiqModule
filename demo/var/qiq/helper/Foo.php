<?php
namespace Qiq\Helper;

class Foo extends Helper
{
    public function __invoke(string $text): string
    {
        return 'Foo:' . $text;
    }
}
