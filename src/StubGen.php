<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use FilesystemIterator;
use Iterator;
use RecursiveDirectoryIterator;
use ReflectionClass;
use ReflectionMethod;
use SplHeap;

use function file_get_contents;
use function file_put_contents;
use function implode;
use function join;
use function lcfirst;
use function sprintf;
use function str_replace;
use function strcmp;

use const PHP_EOL;

final class StubGen
{
    private function docMethod(ReflectionClass $class): string
    {
        $method = new ReflectionMethod($class->getName(), '__invoke');
        $returnType = $method->getReturnType();
        $params = $method->getParameters();
        $paramSigs = [];
        foreach ($params as $param) {
            $paramSigs[] = sprintf('%s $%s', $param->getType(), $param->getName());
        }

        $paramSig = join(', ', $paramSigs);

        return sprintf('%s %s(%s)', $returnType, lcfirst($class->getShortName()), $paramSig);
    }

    private function docBlock(string $dir): string
    {
        $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $sortIterator = new class ($iterator) extends SplHeap
        {
            public function __construct(Iterator $iterator)
            {
                foreach ($iterator as $item) {
                    $this->insert($item);
                }
            }

            public function compare(mixed $b, mixed $a): int
            {
                return strcmp($a->getRealpath(), $b->getRealpath());
            }
        };
        $doc = [];
        foreach ($sortIterator as $fileInfo) {
            $className = sprintf('\Qiq\\Helper\\%s', $fileInfo->getBasename('.php'));
            $class = new ReflectionClass($className);
            if (! $class->isAbstract()) {
                $doc[] = sprintf(' * @method %s', $this->docMethod($class));
            }
        }

        $docString = implode(PHP_EOL, $doc);

        return /** @lang PHP */ <<<EOT
    /**
    {$docString}
     */
    
    EOT;
    }

    private function methodDocGen(string $dir, ReflectionClass $class)
    {
        $contents = file_get_contents($class->getFileName());
        $search = sprintf('class %s', $class->getShortName());

        return str_replace($search, $this->docBlock($dir) . $search, $contents);
    }

    public function __invoke(string $target, string $helperDir): void
    {
        $class = new ReflectionClass($target);
        file_put_contents($class->getFileName(), $this->methodDocGen($helperDir, $class));
    }
}
