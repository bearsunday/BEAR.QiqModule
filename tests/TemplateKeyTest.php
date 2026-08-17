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

        $this->assertSame('Page/Index.php', $key('/app/var/templates/Page/Index.php'));
    }

    public function testTrailingSlashInRoot(): void
    {
        $key = new TemplateKey(['/app/var/templates/']);

        $this->assertSame('Index.php', $key('/app/var/templates/Index.php'));
    }

    public function testDefaultCollectionIsNotInTheKey(): void
    {
        $key = new TemplateKey(['/app/a', 'admin:/app/b']);

        $this->assertSame('x.php', $key('/app/a/x.php'));
        $this->assertSame('admin/x.php', $key('/app/b/x.php'));
    }

    public function testNestedRootsTakeTheLongestMatch(): void
    {
        $outerFirst = new TemplateKey(['/app/a', '/app/a/deep']);
        $innerFirst = new TemplateKey(['/app/a/deep', '/app/a']);

        $this->assertSame('x.php', $outerFirst('/app/a/deep/x.php'));
        $this->assertSame('x.php', $innerFirst('/app/a/deep/x.php'));
    }

    /**
     * Accepted collision: a collection name and a directory under the default root share one key.
     * The build keeps whichever it compiles first, so the loser renders the winner's output.
     */
    public function testCollectionNameCollidesWithADirectory(): void
    {
        $key = new TemplateKey(['/app/t', 'parts:/app/x']);

        $this->assertSame('parts/nav.php', $key('/app/t/parts/nav.php'));
        $this->assertSame('parts/nav.php', $key('/app/x/nav.php'));
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

        $this->assertSame('x.php', $key('/no/such/directory/x.php'));
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
