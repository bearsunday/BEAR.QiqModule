<?php
namespace Qiq\Helper;

class Bar extends Helper
{
    public function __invoke(string $text): string
    {
        return 'Bar:' . $text;
    }
}
