<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Resource\FakeRo;
use PHPUnit\Framework\TestCase;
use Qiq\Compiler;
use Qiq\Compiler\QiqCompiler;
use Ray\Di\Injector;

use function assert;
use function glob;
use function restore_error_handler;
use function set_error_handler;
use function sys_get_temp_dir;
use function uniqid;

use const E_USER_DEPRECATED;

/** The 2.0 form: a cache path, compiled at serve time — deprecated but intact until 3.0 */
class QiqProdModuleCachePathTest extends TestCase
{
    private const RENDERED = 'Hello, World. That was Qiq! And this is PHP, World.' . "\n";

    /** @var non-empty-string */
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-prod-cache-', true);
        FakeTree::copy(__DIR__ . '/Fake/templates', $this->baseDir . '/var/templates');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testCachePathIsDeprecated(): void
    {
        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            $deprecations[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);
        new QiqProdModule($this->baseDir . '/var/tmp/qiq');
        restore_error_handler();

        $this->assertCount(1, $deprecations);
    }

    public function testCompilesAtServeTime(): void
    {
        $cachePath = $this->baseDir . '/var/tmp/qiq';
        $injector = new Injector($this->module($cachePath));

        $this->assertInstanceOf(QiqCompiler::class, $injector->getInstance(Compiler::class));

        $ro = $injector->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);

        $this->assertSame(self::RENDERED, $ro->onGet(['name' => 'World'])->toString());
        $this->assertNotEmpty(glob($cachePath . '/*'));
    }

    private function module(string $cachePath): FakeAppMetaModule
    {
        set_error_handler(static fn (): bool => true, E_USER_DEPRECATED);
        $prodModule = new QiqProdModule($cachePath, new QiqModule($this->baseDir . '/var/templates'));
        restore_error_handler();

        return new FakeAppMetaModule($this->baseDir, $prodModule);
    }
}
