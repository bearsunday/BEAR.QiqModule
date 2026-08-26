<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateOutsideRootException;
use PHPUnit\Framework\TestCase;

class TemplateKeyTest extends TestCase
{
    public function testKeyIsRelativeToTheRoot(): void
    {
        $key = new TemplateKey(['/app/var/templates']);

        $this->assertSame('__DEFAULT__/Page/Index.php', $key('/app/var/templates/Page/Index.php'));
    }

    public function testTrailingSlashInRoot(): void
    {
        $key = new TemplateKey(['/app/var/templates/']);

        $this->assertSame('__DEFAULT__/Index.php', $key('/app/var/templates/Index.php'));
    }

    public function testCollectionNamespacesTheKey(): void
    {
        $key = new TemplateKey(['/app/a', 'admin:/app/b']);

        $this->assertSame('__DEFAULT__/x.php', $key('/app/a/x.php'));
        $this->assertSame('admin/x.php', $key('/app/b/x.php'));
    }

    public function testNestedRootsTakeTheLongestMatch(): void
    {
        $outerFirst = new TemplateKey(['/app/a', '/app/a/deep']);
        $innerFirst = new TemplateKey(['/app/a/deep', '/app/a']);

        $this->assertSame('__DEFAULT__/x.php', $outerFirst('/app/a/deep/x.php'));
        $this->assertSame('__DEFAULT__/x.php', $innerFirst('/app/a/deep/x.php'));
    }

    public function testCollectionNameDoesNotCollideWithADirectory(): void
    {
        $key = new TemplateKey(['/app/t', 'parts:/app/x']);

        $this->assertSame('__DEFAULT__/parts/nav.php', $key('/app/t/parts/nav.php'));
        $this->assertSame('parts/nav.php', $key('/app/x/nav.php'));
    }

    public function testNameAndPathAgreeOnTheKey(): void
    {
        $key = new TemplateKey(['/app/t', 'parts:/app/x']);

        $this->assertSame($key('/app/t/nav.php'), TemplateKey::ofName('nav', '.php'));
        $this->assertSame($key('/app/x/nav.php'), TemplateKey::ofName('parts:nav', '.php'));
    }

    public function testRelocatedTreeKeepsTheKey(): void
    {
        $build = new TemplateKey(['/build/checkout/var/templates']);
        $boot = new TemplateKey(['/srv/release-7/var/templates']);

        $this->assertSame(
            $build('/build/checkout/var/templates/Page/Index.php'),
            $boot('/srv/release-7/var/templates/Page/Index.php'),
        );
    }

    public function testRootIsNotResolvedOnTheFilesystem(): void
    {
        $key = new TemplateKey(['/no/such/directory']);

        $this->assertSame('__DEFAULT__/x.php', $key('/no/such/directory/x.php'));
    }

    public function testSourceOutsideEveryRoot(): void
    {
        $key = new TemplateKey(['/app/a']);

        $this->expectException(TemplateOutsideRootException::class);
        $key('/app/b/x.php');
    }

    public function testRootPrefixIsNotMatchedMidSegment(): void
    {
        $key = new TemplateKey(['/app/a']);

        $this->expectException(TemplateOutsideRootException::class);
        $key('/app/about/x.php');
    }
}
