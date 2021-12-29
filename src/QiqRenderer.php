<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;
use Ray\Di\Di\Named;
use ReflectionClass;

use function assert;
use function is_array;

final class QiqRenderer implements RenderInterface
{
    /**
     * @Named("qiq_template_dir")
     */
    #Named['qiq_template_dir']
    public function __construct(private string $templateDir)
    {
    }

    public function render(ResourceObject $ro): string
    {
        $class = new ReflectionClass($ro);
        $tpl = Template::new($this->templateDir);
        $name = $class->getShortName();
        $tpl->setView($name);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }
}
