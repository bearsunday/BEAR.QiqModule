<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateNotWrittenException;
use PHPUnit\Framework\TestCase;

use function chmod;
use function file_get_contents;
use function file_put_contents;
use function is_writable;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

class QiqCompileStepTest extends TestCase
{
    private const TEMPLATES = __DIR__ . '/Fake/templates';
    private const TEMPLATE_COUNT = 4;

    private string $baseDir;

    /** @var non-empty-string */
    private string $stepDir;

    protected function setUp(): void
    {
        $this->baseDir = sys_get_temp_dir() . '/' . uniqid('qiq-step-', true);
        $this->stepDir = $this->baseDir . '/' . QiqCompileStep::NAME;
        mkdir($this->stepDir, 0777, true);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        chmod($this->stepDir, 0777);
        FakeTree::delete($this->baseDir);
        parent::tearDown();
    }

    public function testWritesRelativeToTheStepDir(): void
    {
        $step = new QiqCompileStep([self::TEMPLATES], '.php');

        $count = $step($this->stepDir);

        $this->assertSame(self::TEMPLATE_COUNT, $count);
        $this->assertFileExists($this->stepDir . '/__DEFAULT__/FakeRo.php');
        $this->assertFileExists($this->stepDir . '/__DEFAULT__/SubDirectory/FakeSub.php');
        $this->assertStringContainsString('$this->h($name)', (string) file_get_contents($this->stepDir . '/__DEFAULT__/FakeRo.php'));
    }

    public function testMissingRootIsNotAnError(): void
    {
        $step = new QiqCompileStep([self::TEMPLATES, '/no/such/directory'], '.php');

        $this->assertSame(self::TEMPLATE_COUNT, $step($this->stepDir));
    }

    public function testNoTemplateDirectoryAtAll(): void
    {
        $step = new QiqCompileStep(['/no/such/directory'], '.php');

        $this->assertSame(0, $step($this->stepDir));
    }

    public function testFirstRootWins(): void
    {
        $step = new QiqCompileStep($this->twoRootsSharingATemplate(), '.php');

        $count = $step($this->stepDir);

        $this->assertSame(1, $count);
        $this->assertSame('first', file_get_contents($this->stepDir . '/__DEFAULT__/Dup.php'));
    }

    public function testStaleArtifactsAreRemoved(): void
    {
        file_put_contents($this->stepDir . '/Removed.php', 'stale');
        $step = new QiqCompileStep([self::TEMPLATES], '.php');

        $step($this->stepDir);

        $this->assertFileDoesNotExist($this->stepDir . '/Removed.php');
    }

    public function testUnwritableStepDir(): void
    {
        chmod($this->stepDir, 0555);
        if (is_writable($this->stepDir)) {
            $this->markTestSkipped('the step directory stays writable regardless of its mode');
        }

        $step = new QiqCompileStep([self::TEMPLATES], '.php');

        $this->expectException(TemplateNotWrittenException::class);
        $step($this->stepDir);
    }

    /** @return list<string> */
    private function twoRootsSharingATemplate(): array
    {
        $roots = [$this->baseDir . '/first', $this->baseDir . '/second'];
        foreach ($roots as $root) {
            mkdir($root);
        }

        file_put_contents($roots[0] . '/Dup.php', 'first');
        file_put_contents($roots[1] . '/Dup.php', 'second');

        return $roots;
    }
}
