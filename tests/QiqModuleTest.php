<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use PHPUnit\Framework\TestCase;

class QiqModuleTest extends TestCase
{
    protected QiqModule $qiqModule;

    protected function setUp(): void
    {
        $this->qiqModule = new QiqModule('');
    }

    public function testIsInstanceOfQiqModule(): void
    {
        $actual = $this->qiqModule;
        $this->assertInstanceOf(QiqModule::class, $actual);
    }
}
