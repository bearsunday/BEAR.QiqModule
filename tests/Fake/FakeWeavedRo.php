<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Resource\FakeRo;
use Ray\Aop\WeavedInterface;

class FakeWeavedRo extends FakeRo implements WeavedInterface
{
}
