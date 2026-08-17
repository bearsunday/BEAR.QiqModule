<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateNotCompiledException;
use BEAR\QiqModule\Resource\FakeRo;
use PHPUnit\Framework\TestCase;
use Qiq\Compiler;
use Ray\Di\Injector;

use function assert;
use function mkdir;
use function rename;
use function sys_get_temp_dir;
use function uniqid;

class QiqServeCompilerTest extends TestCase
{
    private string $baseDir;
    private string $buildDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-serve-', true);
        $this->buildDir = $this->baseDir . '/build';
        mkdir($this->buildDir . '/' . QiqCompileStep::NAME, 0777, true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testUncompiledTemplate(): void
    {
        $compiler = new QiqServeCompiler($this->buildDir, [__DIR__ . '/Fake/templates']);

        $this->expectException(TemplateNotCompiledException::class);
        $compiler->compile(__DIR__ . '/Fake/templates/FakeRo.php');
    }

    public function testClearKeepsArtifacts(): void
    {
        $templates = __DIR__ . '/Fake/templates';
        (new QiqCompileStep([$templates], '.php'))($this->buildDir . '/' . QiqCompileStep::NAME);
        $compiler = new QiqServeCompiler($this->buildDir, [$templates]);

        $compiler->clear();

        $this->assertSame(
            $this->buildDir . '/' . QiqCompileStep::NAME . '/FakeRo.php',
            $compiler->compile($templates . '/FakeRo.php'),
        );
    }

    public function testRelocatedTreeRenders(): void
    {
        FakeTree::copy(__DIR__ . '/Fake/templates', $this->baseDir . '/before/templates');
        (new QiqCompileStep([$this->baseDir . '/before/templates'], '.php'))(
            $this->buildDir . '/' . QiqCompileStep::NAME,
        );
        rename($this->baseDir . '/before', $this->baseDir . '/after');

        $injector = $this->prodInjector($this->baseDir . '/after/templates', null);

        $this->assertSame(
            'Hello, World. That was Qiq! And this is PHP, World.' . "\n",
            $this->render($injector),
        );
    }

    public function testCachePathStandsInForBuildDir(): void
    {
        $templates = __DIR__ . '/Fake/templates';
        (new QiqCompileStep([$templates], '.php'))($this->buildDir . '/' . QiqCompileStep::NAME);

        $injector = $this->prodInjector($templates, $this->buildDir);

        $this->assertSame(
            'Hello, World. That was Qiq! And this is PHP, World.' . "\n",
            $this->render($injector),
        );
    }

    private function prodInjector(string $templates, string|null $cachePath): Injector
    {
        // the prod module has to be the outer one to take over the Compiler binding
        $module = new FakeBuildDirModule(
            $this->buildDir,
            new QiqProdModule($cachePath, new QiqModule($templates)),
        );
        $this->assertInstanceOf(QiqServeCompiler::class, (new Injector($module))->getInstance(Compiler::class));

        return new Injector($module);
    }

    private function render(Injector $injector): string
    {
        $ro = $injector->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);

        return (string) $ro->onGet(['name' => 'World']);
    }
}
