<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\ApiDoc\Ref;
use FilesystemIterator;
use Iterator;
use Qiq\Helper\Helper;
use RecursiveDirectoryIterator;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
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
    /**
     * @param ReflectionClass<Helper> $class
     */
    private function docMethod(ReflectionClass $class): string
    {
        $method = new ReflectionMethod($class->getName(), '__invoke');
        $returnType = (string) $method->getReturnType();
        $params = $method->getParameters();
        $paramSigs = [];
        foreach ($params as $param) {
            $paramSigs[] = sprintf('%s $%s', (string) $param->getType(), $param->getName());
        }

        $paramSig = join(', ', $paramSigs);

        return sprintf('%s %s(%s)', $returnType, lcfirst($class->getShortName()), $paramSig);
    }

    private function docBlock(string $dir): string
    {
        $iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $sortIterator = new class ($iterator) extends SplHeap
        {
            /**
             * @param Iterator<string> $iterator
             */
            public function __construct(Iterator $iterator)
            {
                foreach ($iterator as $item) {
                    $this->insert($item);
                }
            }

            public function compare(mixed $b, mixed $a): int
            {
                assert($a instanceof SplFileInfo);
                assert($b instanceof SplFileInfo);
                return strcmp((string) $a->getRealpath(), (string) $b->getRealpath());
            }
        };
        $doc = [];
        foreach ($sortIterator as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            $className = sprintf('\Qiq\\Helper\\%s', $fileInfo->getBasename('.php'));
            assert(class_exists($className));
            /** @var ReflectionClass<Helper> $class */
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

    /**
     * @param ReflectionClass<Helper> $class
     */
    private function methodDocGen(string $dir, ReflectionClass $class): string
    {
        $contents = (string) file_get_contents((string) $class->getFileName());
        $search = sprintf('class %s', $class->getShortName());

        return str_replace($search, $this->docBlock($dir) . $search, $contents);
    }

    /**
     * @param class-string $target
     */
    public function __invoke(string $target, string $helperDir): void
    {
        /** @var ReflectionClass<Helper> $class */
        $class = new ReflectionClass($target);
        file_put_contents((string) $class->getFileName(), $this->methodDocGen($helperDir, $class));
    }
}
