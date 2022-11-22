<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Resource;

use BEAR\Resource\ResourceObject;

class FakeRo extends ResourceObject
{
    /** @param array<string, int|string> $data */
    public function onGet(array $data): ResourceObject
    {
        $this->body = $data;

        return $this;
    }
}
