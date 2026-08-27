<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Exception;

/** The build could not write an artifact, and has to fail rather than report a phantom count */
final class TemplateNotWrittenException extends QiqModuleException
{
}
