<?php
/**
 * Generate method phpdoc from the helper class and make it the phpdoc of the target class
 *
 * @example QIQ_STUB_TARGET=MyQiq QIQ_STUB_HELPER_DIR=/path/to/helper stub_gen.php
 */

require dirname(__DIR__, 2) . '/vendor/autoload.php';

class SortedIterator extends SplHeap
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
}

function docMethod(ReflectionClass $class): string
{
    $method = new ReflectionMethod($class->getName(), '__invoke');
    $returnType = $method->getReturnType();
    $params = $method->getParameters();
    $paramSigs = [];
    foreach ($params as $param) {
        $paramSigs[] = sprintf('%s $%s', $param->getType(), $param->getName());
    }
    $paramSig = join(', ', $paramSigs);
    $doc = sprintf('%s %s(%s)', $returnType, lcfirst($class->getShortName()), $paramSig);

    return $doc;
}

function docBlock(string $dir): string
{
    $doc = [];
    foreach (new SortedIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $fileInfo) {
        $className = sprintf('\Qiq\\Helper\\%s', $fileInfo->getBasename('.php'));
        $class = new ReflectionClass($className);
        if (!$class->isAbstract()) {
            $doc[] = sprintf(' * @method %s', docMethod($class));
        }
    }

    $docString = implode(PHP_EOL, $doc);
    return /** @lang PHP */ <<<EOT
/**
{$docString}
 */

EOT;

}

function methodDocGen(string $dir, ReflectionClass $class)
{
    $contents = file_get_contents($class->getFileName());
    $search = sprintf("class %s", $class->getShortName());
    $stubedContents = str_replace($search, docBlock($dir) . $search, $contents);

    return $stubedContents;
}

$target = getenv('QIQ_STUB_TARGET') ?: '\Qiq\\Template';
$helperDir = getenv('QIQ_STUB_HELPER_DIR') ?: dirname(__DIR__) . '/src/Helper';

$class = new ReflectionClass($target);
file_put_contents($class->getFileName(), methodDocGen($helperDir, $class));
