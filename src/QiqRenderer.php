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
    public function __construct(
        #[Named('qiq_template_dir')] private string $templateDir,
        #[Named('qiq_cache_path')] private ?string $cachePath = null,
        #[Named('qiq_layout')] private ?string $layout = null,
    ) {
    }

    public function render(ResourceObject $ro): string
    {
        $class = new ReflectionClass($ro);
        $tpl = Template::new(paths: $this->templateDir, cachePath: $this->cachePath);
        $name = $class->getShortName();
        $tpl->setView($name);
        $tpl->setLayout($this->layout);
        assert(is_array($ro->body));
        $tpl->setData($ro->body);

        return $tpl();
    }
}
