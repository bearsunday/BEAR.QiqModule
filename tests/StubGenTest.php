<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function copy;
use function file_get_contents;

class StubGenTest extends TestCase
{
    public function testInvoke()
    {
        copy(__DIR__ . '/Fake/FakeQiq.php.dist', __DIR__ . '/Fake/FakeQiq.php');
        (new StubGen())(FakeQiq::class, __DIR__ . '/Fake/helper');
        $fileText =  file_get_contents((new ReflectionClass(FakeQiq::class))->getFileName());
        $this->assertStringContainsString('@method string bar(string $barText)  ', $fileText);
    }
}
