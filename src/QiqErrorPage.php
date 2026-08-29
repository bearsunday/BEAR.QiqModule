<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Types;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/** @psalm-import-type Headers from Types */
final class QiqErrorPage extends ResourceObject
{
    /** @var Headers */
    public $headers = ['content-type' => 'text/html; charset=utf-8'];

    /** @var RenderInterface|null */
    protected $renderer;

    /** @return list<string> */
    public function __sleep(): array
    {
        return ['renderer'];
    }

    #[Inject]
    public function setRenderer(#[Named('error_page')] RenderInterface $renderer): ResourceObject
    {
        return parent::setRenderer($renderer);
    }
}
