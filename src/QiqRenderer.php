<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Annotation\Layout;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;
use Ray\Di\Di\Named;
use ReflectionClass;

use function assert;
use function is_array;
use function is_string;

final class QiqRenderer implements RenderInterface
{
    public function __construct(
        #[Named('qiq_template_dir')] private string $templateDir,
        #[Named('qiq_cache_path')] private ?string $cachePath = null,
        #[Named('qiq_layout')] private ?string $layout = null,
    ) {
    }

    public function render(ResourceObject $ro): string
    {
        $class = new ReflectionClass($ro);
        $methods = $class->getMethods();
        $tpl = Template::new(paths: $this->templateDir, cachePath: $this->cachePath);
        $name = $class->getShortName();
        $tpl->setView($name);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Layout::class);
            foreach ($attributes as $attribute) {
                $arguments = $attribute->getArguments();
                foreach ($arguments as $argument) {
                    if (! is_string($argument)) {
                        continue;
                    }

                    $this->layout = $argument;
                }
            }
        }

        $tpl->setLayout($this->layout);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }
}
