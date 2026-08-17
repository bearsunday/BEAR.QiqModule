<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;
use Ray\Di\Di\Named;

use function assert;
use function is_array;

final class QiqErrorPageRenderer implements RenderInterface
{
    public function __construct(
        private Template $template,
        #[Named('qiq_error_view_name')] private string|null $errorViewName = null,
    ) {
    }

    public function render(ResourceObject $ro): string
    {
        // the bound Template is a singleton carrying render state
        $tpl = clone $this->template;
        $tpl->setView($this->errorViewName);
        assert(is_array($ro->body));
        $status = $ro->body['status'];
        assert(is_array($status));
        $tpl->setData($status);
        $ro->view = $tpl();

        return $ro->view;
    }
}
