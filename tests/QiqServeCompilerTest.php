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
    private const TEMPLATES = __DIR__ . '/Fake/templates';

    private string $baseDir;

    /** @var non-empty-string */
    private string $appDir;

    /** @var non-empty-string */
    private string $stepDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-serve-', true);
        $this->appDir = $this->baseDir . '/app';
        $this->stepDir = $this->stepDir($this->appDir);
        mkdir($this->stepDir, 0777, true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testUncompiledTemplate(): void
    {
        $compiler = new QiqServeCompiler(new FakeAppMeta($this->appDir), [self::TEMPLATES]);

        $this->expectException(TemplateNotCompiledException::class);
        $compiler->compile(self::TEMPLATES . '/FakeRo.php');
    }

    public function testStepDirComesFromAppDir(): void
    {
        (new QiqCompileStep([self::TEMPLATES], '.php'))($this->stepDir);
        $compiler = new QiqServeCompiler(new FakeAppMeta($this->appDir), [self::TEMPLATES]);

        $this->assertSame(
            $this->stepDir . '/FakeRo.php',
            $compiler->compile(self::TEMPLATES . '/FakeRo.php'),
        );
    }

    public function testClearKeepsArtifacts(): void
    {
        (new QiqCompileStep([self::TEMPLATES], '.php'))($this->stepDir);
        $compiler = new QiqServeCompiler(new FakeAppMeta($this->appDir), [self::TEMPLATES]);

        $compiler->clear();

        $this->assertSame(
            $this->stepDir . '/FakeRo.php',
            $compiler->compile(self::TEMPLATES . '/FakeRo.php'),
        );
    }

    public function testRelocatedTreeRenders(): void
    {
        $before = $this->baseDir . '/before';
        FakeTree::copy(self::TEMPLATES, $before . '/templates');
        mkdir($this->stepDir($before), 0777, true);
        (new QiqCompileStep([$before . '/templates'], '.php'))($this->stepDir($before));
        rename($before, $this->baseDir . '/after');

        $after = $this->baseDir . '/after';
        $injector = $this->prodInjector($after, $after . '/templates');

        $this->assertSame(
            'Hello, World. That was Qiq! And this is PHP, World.' . "\n",
            $this->render($injector),
        );
    }

    /**
     * @param non-empty-string $appDir
     *
     * @return non-empty-string
     */
    private function stepDir(string $appDir): string
    {
        return $appDir . '/var/build/' . QiqCompileStep::NAME;
    }

    /** @param non-empty-string $appDir */
    private function prodInjector(string $appDir, string $templates): Injector
    {
        // the prod module has to be the outer one to take over the Compiler binding
        $module = new FakeAppMetaModule(
            $appDir,
            new QiqProdModule(new QiqModule($templates)),
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
