<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;

use function assert;
use function dirname;
use function is_array;

class QiqErrorPageRenderer implements RenderInterface
{
    public function render(ResourceObject $ro): string
    {
        $tpl = Template::new(dirname(__DIR__) . '/tests/Fake/templates');
        $tpl->setView('Error');
        assert(is_array($ro->body));
        $tpl->setData($ro->body['status']);
        $ro->view = $tpl();

        return $ro->view;
    }
}
