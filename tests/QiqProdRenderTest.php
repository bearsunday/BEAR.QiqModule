<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\QiqModule\Exception\TemplateNotCompiledException;
use BEAR\QiqModule\Resource\FakeRo;
use BEAR\Sunday\Compile\CompileStepInterface;
use PHPUnit\Framework\TestCase;
use Qiq\Catalog;
use Ray\Di\Injector;

use function assert;
use function mkdir;
use function rename;
use function sys_get_temp_dir;
use function uniqid;

class QiqProdRenderTest extends TestCase
{
    private const RENDERED = 'Hello, World. That was Qiq! And this is PHP, World.' . "\n";

    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-prod-render-', true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testUncompiledTemplate(): void
    {
        $injector = $this->prodInjector($this->app('app'));

        $this->expectException(TemplateNotCompiledException::class);
        $this->render($injector);
    }

    public function testTheStepAndTheCatalogMeetInTheSameDirectory(): void
    {
        $injector = $this->prodInjector($this->app('app'));

        $this->compile($injector);

        $this->assertSame(self::RENDERED, $this->render($injector));
    }

    /** What an archive ships: the artifacts without the templates they were compiled from */
    public function testRendersWithoutTheSourceTree(): void
    {
        $appDir = $this->app('app');
        $injector = $this->prodInjector($appDir);
        $this->compile($injector);

        FakeTree::delete($appDir . '/var/templates');

        $this->assertSame(self::RENDERED, $this->render($injector));
    }

    public function testRelocatedTreeRenders(): void
    {
        $this->compile($this->prodInjector($this->app('before')));
        rename($this->baseDir . '/before', $this->baseDir . '/after');

        $injector = $this->prodInjector($this->baseDir . '/after');

        $this->assertSame(self::RENDERED, $this->render($injector));
    }

    /**
     * The caller side of CompileStepInterface: bear/package makes {buildDir}/{binding key} and invokes.
     * Deriving the directory any other way is what this test is here to catch, so it must not be spelled
     * out a second time.
     */
    private function compile(Injector $injector): void
    {
        $meta = $injector->getInstance(AbstractAppMeta::class);
        assert($meta instanceof AbstractAppMeta);
        $holder = $injector->getInstance(FakeCompileSteps::class);
        assert($holder instanceof FakeCompileSteps);

        foreach ($holder->steps as $key => $step) {
            assert($step instanceof CompileStepInterface);
            $stepDir = $meta->buildDir . '/' . $key;
            mkdir($stepDir, 0777, true);
            $step($stepDir);
        }
    }

    /** @return non-empty-string appDir holding a copy of the templates */
    private function app(string $name): string
    {
        $appDir = $this->baseDir . '/' . $name;
        FakeTree::copy(__DIR__ . '/Fake/templates', $appDir . '/var/templates');

        return $appDir;
    }

    /** @param non-empty-string $appDir */
    private function prodInjector(string $appDir): Injector
    {
        // the prod module has to be the outer one to take over the Catalog binding
        $module = new FakeAppMetaModule(
            $appDir,
            new QiqProdModule(new QiqModule($appDir . '/var/templates')),
        );
        $this->assertInstanceOf(QiqProdCatalog::class, (new Injector($module))->getInstance(Catalog::class));

        return new Injector($module);
    }

    /** ResourceObject::__toString() turns a render failure into a warning, so render through toString() */
    private function render(Injector $injector): string
    {
        $ro = $injector->getInstance(FakeRo::class);
        assert($ro instanceof FakeRo);

        return $ro->onGet(['name' => 'World'])->toString();
    }
}
