<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Exception\TemplateOutsideRootException;

use function rtrim;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;

use const DIRECTORY_SEPARATOR;
use const PHP_OS_FAMILY;

/**
 * Cache key of a template: `{collection}/{path relative to the root it lives under}`
 *
 * Keys have to survive relocation of the tree, so no absolute path may enter them.
 * The default collection keeps its `__DEFAULT__` name in the key, so a directory named
 * like a collection under the default root cannot shadow that collection's templates.
 */
final class TemplateKey
{
    /** Collection name Qiq gives to paths with no `collection:` prefix */
    public const DEFAULT_COLLECTION = '__DEFAULT__';

    /** @var array<string, list<string>> */
    private array $roots = [];

    /** @param list<string> $paths `qiq_paths` values: `path` or `collection:path` */
    public function __construct(array $paths)
    {
        foreach ($paths as $spec) {
            [$collection, $path] = $this->split($spec);
            $this->roots[$collection][] = $this->fixPath($path);
        }
    }

    /** @throws TemplateOutsideRootException */
    public function __invoke(string $source): string
    {
        $path = str_replace('\\', '/', $source);
        $key = null;
        $matched = 0;
        foreach ($this->roots as $collection => $roots) {
            foreach ($roots as $root) {
                $prefix = rtrim(str_replace('\\', '/', $root), '/') . '/';
                $length = strlen($prefix);
                // longest match: a nested root would otherwise be shadowed by its parent
                if (! str_starts_with($path, $prefix) || $length <= $matched) {
                    continue;
                }

                $matched = $length;
                $key = $collection . '/' . substr($path, $length);
            }
        }

        if ($key === null) {
            throw new TemplateOutsideRootException($source);
        }

        return $key;
    }

    /**
     * The same key for a template addressed by the name Qiq renders: `collection:sub/Name`
     *
     * @see self::__invoke() the write side, which derives the key from a source path
     */
    public static function ofName(string $name, string $extension): string
    {
        [$collection, $path] = self::split($name);

        return $collection . '/' . $path . $extension;
    }

    /** @return array<string, list<string>> */
    public function roots(): array
    {
        return $this->roots;
    }

    /**
     * @return array{string, string}
     *
     * @see \Qiq\Catalog::split() protected, and injecting the Catalog would loop through Compiler
     */
    private static function split(string $spec): array
    {
        $offset = PHP_OS_FAMILY === 'Windows' ? 2 : 0;
        $pos = strpos($spec, ':', $offset);
        if ($pos === false || $pos === 0) {
            return [self::DEFAULT_COLLECTION, $spec];
        }

        return [substr($spec, 0, $pos), substr($spec, $pos + 1)];
    }

    /** @see \Qiq\Catalog::fixPath() */
    private function fixPath(string $path): string
    {
        return rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }
}
