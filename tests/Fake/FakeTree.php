<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function copy;
use function is_dir;
use function mkdir;
use function rmdir;
use function strlen;
use function substr;
use function unlink;

final class FakeTree
{
    public static function copy(string $from, string $to): void
    {
        mkdir($to, 0777, true);
        foreach (self::walk($from, RecursiveIteratorIterator::SELF_FIRST) as $file) {
            $target = $to . '/' . substr($file->getPathname(), strlen($from) + 1);
            $file->isDir() ? mkdir($target, 0777, true) : copy($file->getPathname(), $target);
        }
    }

    public static function delete(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (self::walk($dir, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }

    /** @return iterable<SplFileInfo> */
    private static function walk(string $dir, int $mode): iterable
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            $mode,
        );
    }
}
