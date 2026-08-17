<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Exception;

/** The template has no build artifact: run the compile step before serving */
final class TemplateNotCompiledException extends QiqModuleException
{
}
