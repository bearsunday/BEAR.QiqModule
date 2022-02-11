<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function copy;
use function file_get_contents;

class StubGenTest extends TestCase
{
    protected function tearDown(): void
    {
        copy(__DIR__ . '/Fake/FakeQiq.php.dist', __DIR__ . '/Fake/FakeQiq.php');
    }

    public function testInvoke(): void
    {
        copy(__DIR__ . '/Fake/FakeQiq.php.dist', __DIR__ . '/Fake/FakeQiq.php');
        (new StubGen())(FakeQiq::class, __DIR__ . '/Fake/helper');
        $fileText = (string) file_get_contents((string) (new ReflectionClass(FakeQiq::class))->getFileName());
        $this->assertStringContainsString('@method string foo(string $fooText)', $fileText);
    }
}
