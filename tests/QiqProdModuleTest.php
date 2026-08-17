<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use PHPUnit\Framework\TestCase;
use Qiq\Compiler;
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
        $module = new FakeAppMetaModule('/path/to/app', new QiqProdModule(new QiqModule('/no/such/templates')));
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

    public function testCompilerReadsTheBuiltArtifacts(): void
    {
        $compiler = $this->injector->getInstance(Compiler::class);

        $this->assertInstanceOf(QiqServeCompiler::class, $compiler);
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

    private function step(): CompileStepInterface
    {
        $holder = $this->injector->getInstance(FakeCompileSteps::class);
        assert($holder instanceof FakeCompileSteps);
        $step = iterator_to_array($holder->steps)[QiqCompileStep::NAME];
        assert($step instanceof CompileStepInterface);

        return $step;
    }
}
