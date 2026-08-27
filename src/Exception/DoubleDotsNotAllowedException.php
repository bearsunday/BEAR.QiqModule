<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Exception;

/**
 * The template spec or name contains `..`, which Qiq's own Catalog::split() rejects
 *
 * A `..` in a name would resolve outside the build directory, so the key side
 * refuses it the same way the source side does.
 */
final class DoubleDotsNotAllowedException extends QiqModuleException
{
}
