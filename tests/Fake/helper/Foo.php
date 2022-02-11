<?php
namespace Qiq\Helper;

class Foo extends Helper
{
    public function __invoke(string $fooText): string
    {
        return 'Foo:' . $fooText;
    }
}
