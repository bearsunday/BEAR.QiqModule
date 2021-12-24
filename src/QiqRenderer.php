<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;

use function dirname;

final class QiqRenderer implements RenderInterface
{
    public function render(ResourceObject $ro): string
    {
        $tpl = Template::new(dirname(__DIR__) . '/tests/Fake/templates');
        $tpl->setView('hello');
        $tpl->setData(['name' => 'World']);

        return $tpl();
    }
}
