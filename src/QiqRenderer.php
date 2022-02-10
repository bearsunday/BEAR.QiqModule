<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\TemplateCore;
use ReflectionClass;

use function assert;
use function is_array;

final class QiqRenderer implements RenderInterface
{
    public function __construct(
        private TemplateCore $template
    ) {
    }

    public function render(ResourceObject $ro): string
    {
        $class = new ReflectionClass($ro);
        $tpl = $this->template;
        $name = $class->getShortName();
        $tpl->setView($name);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }
}
