<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Resource;

use BEAR\Resource\ResourceObject;

class FakeNullRo extends ResourceObject
{
    public function onGet(): ResourceObject
    {
        $this->body = null;

        return $this;
    }
}
