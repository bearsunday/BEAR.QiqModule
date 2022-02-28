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
use function is_string;
use function str_replace;
use function strpos;
use function substr;

final class QiqRenderer implements RenderInterface
{
    public function __construct(
        private TemplateCore $template
    ) {
    }

    public function render(ResourceObject $ro): string
    {
        $class = $this->getReflection($ro);
        $fileName = $class->getFileName();
        assert(is_string($fileName));
        $path = $this->getTemplatePath($fileName);
        $tpl = $this->template;
        $tpl->setView($path);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }

    /** @return ReflectionClass<ResourceObject>|ReflectionClass<object> */
    private function getReflection(ResourceObject $ro): ReflectionClass
    {
        if ($ro instanceof WeavedInterface) {
            $parentClass = (new ReflectionClass($ro))->getParentClass();
            assert($parentClass instanceof ReflectionClass);

            return $parentClass;
        }

        return new ReflectionClass($ro);
    }

    private function getTemplatePath(string $resourceFilePath): string
    {
        $pos = strpos($resourceFilePath, '/Resource/');
        $relativePath = substr($resourceFilePath, (int) $pos + 10);

        return str_replace('.php', '', $relativePath);
    }
}
