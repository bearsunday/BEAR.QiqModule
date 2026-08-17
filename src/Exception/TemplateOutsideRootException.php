<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Exception;

/** The template is under none of the `qiq_paths` roots, so no relocatable key describes it */
final class TemplateOutsideRootException extends QiqModuleException
{
}
