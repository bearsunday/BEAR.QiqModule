<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\AppMeta\AbstractAppMeta;

final class FakeAppMeta extends AbstractAppMeta
{
    /** The build directory carries it, so a read side that derives from appDir alone loses it */
    public const CONTEXT = 'prod-app';

    /** @param non-empty-string $appDir */
    public function __construct(string $appDir)
    {
        $this->name = 'BEAR\QiqModule';
        $this->appDir = $appDir;
        $this->tmpDir = $appDir . '/var/tmp';
        $this->logDir = $appDir . '/var/log';
        $this->buildDir = $appDir . '/var/build/' . self::CONTEXT;
    }
}
