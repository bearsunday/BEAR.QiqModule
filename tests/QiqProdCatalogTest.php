<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateNotCompiledException;
use PHPUnit\Framework\TestCase;
use Qiq\Compiler\NonCompiler;

use function file_put_contents;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class QiqProdCatalogTest extends TestCase
{
    private string $baseDir;
    private string $compiledDir;
    private QiqProdCatalog $catalog;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-prod-catalog-', true);
        /** @var non-empty-string $appDir */
        $appDir = $this->baseDir . '/app';
        $meta = new FakeAppMeta($appDir);
        $this->compiledDir = $meta->buildDir . '/' . QiqCompileStep::NAME;
        mkdir($this->compiledDir . '/__DEFAULT__', 0777, true);
        mkdir($this->compiledDir . '/theme/SubDirectory', 0777, true);
        file_put_contents($this->compiledDir . '/__DEFAULT__/FakeRo.php', 'compiled');
        file_put_contents($this->compiledDir . '/theme/SubDirectory/FakeSub.php', 'themed');
        $this->catalog = new QiqProdCatalog($meta, new NonCompiler(), '.php');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testResolvesByName(): void
    {
        $this->assertSame($this->compiledDir . '/__DEFAULT__/FakeRo.php', $this->catalog->getCompiled('FakeRo'));
    }

    public function testCollectionIsASubDirectory(): void
    {
        $compiled = $this->catalog->getCompiled('theme:SubDirectory/FakeSub');

        $this->assertSame($this->compiledDir . '/theme/SubDirectory/FakeSub.php', $compiled);
    }

    public function testNotCompiled(): void
    {
        $this->expectException(TemplateNotCompiledException::class);
        $this->catalog->getCompiled('NoSuchTemplate');
    }

    public function testHas(): void
    {
        $this->assertTrue($this->catalog->has('FakeRo'));
        $this->assertFalse($this->catalog->has('NoSuchTemplate'));
    }
}
