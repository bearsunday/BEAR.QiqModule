<?php

declare(strict_types=1);

namespace BEAR\QiqModule\Resource\SubDirectory;

use BEAR\Resource\ResourceObject;

class FakeSub extends ResourceObject
{
    /**
     * @param array<string, int|string> $data
     */
    public function onGet(array $data): ResourceObject
    {
        $this->body = $data;

        return $this;
    }
}
