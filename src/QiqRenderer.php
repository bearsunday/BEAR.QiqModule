<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\TemplateCore;
use Ray\Aop\WeavedInterface;
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
        $class = $this->getReflection($ro);
        $name = $class->getShortName();
        $tpl = $this->template;
        $tpl->setView($name);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }

    /** @return ReflectionClass<ResourceObject> */
    private function getReflection(ResourceObject $ro): ReflectionClass
    {
        if ($ro instanceof WeavedInterface) {
            return (new ReflectionClass($ro))->getParentClass();
        }

        return new ReflectionClass($ro);
    }
}
