<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Compile\CompileStepInterface;
use Ray\Di\Di\Set;
use Ray\Di\MultiBinding\Map;

final class FakeCompileSteps
{
    /** @param Map<CompileStepInterface> $steps */
    public function __construct(
        #[Set(CompileStepInterface::class)]
        public Map $steps,
    ) {
    }
}
