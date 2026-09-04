<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use PHPUnit\Framework\TestCase;
use Qiq\Catalog;
use Qiq\Compiler;
use Qiq\Compiler\NonCompiler;
use Ray\Di\Injector;

use function assert;
use function iterator_to_array;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class QiqProdModuleTest extends TestCase
{
    private Injector $injector;

    /** @var non-empty-string */
    private string $stepDir;

    protected function setUp(): void
    {
        $module = new FakeAppMetaModule('/path/to/app', new QiqProdModule(module: new QiqModule('/no/such/templates')));
        $this->injector = new Injector($module);
        $this->stepDir = sys_get_temp_dir() . '/' . uniqid('qiq-prod-', true);
        mkdir($this->stepDir, 0777, true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->stepDir);
        parent::tearDown();
    }

    public function testTemplatesAreResolvedByName(): void
    {
        $catalog = $this->injector->getInstance(Catalog::class);

        $this->assertInstanceOf(QiqProdCatalog::class, $catalog);
    }

    /** Nothing compiles at serve time, and a compiler that could write has no place in the graph */
    public function testNothingCompiles(): void
    {
        $compiler = $this->injector->getInstance(Compiler::class);

        $this->assertInstanceOf(NonCompiler::class, $compiler);
    }

    public function testCompileStepIsBoundToItsName(): void
    {
        $this->assertInstanceOf(QiqCompileStep::class, $this->step());
    }

    /** A skeleton has the module installed before it has any template */
    public function testStepWithoutTemplates(): void
    {
        $step = $this->step();

        $this->assertSame(0, $step($this->stepDir));
    }

    /** ProdModule installs the module for every context; one without QiqModule, such as prod-cli-hal-app, has nothing to compile */
    public function testStepWithoutQiqModule(): void
    {
        $injector = new Injector(new FakeAppMetaModule('/path/to/app', new QiqProdModule()));
        $step = $this->stepOf($injector);

        $this->assertSame(0, $step($this->stepDir));
    }

    private function step(): CompileStepInterface
    {
        return $this->stepOf($this->injector);
    }

    private function stepOf(Injector $injector): CompileStepInterface
    {
        $holder = $injector->getInstance(FakeCompileSteps::class);
        assert($holder instanceof FakeCompileSteps);
        $step = iterator_to_array($holder->steps)[QiqCompileStep::NAME];
        assert($step instanceof CompileStepInterface);

        return $step;
    }
}
