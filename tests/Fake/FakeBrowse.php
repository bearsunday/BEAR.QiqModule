<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\ResourceObject;

#[Layout('/layout/default')]
final class FakeBrowse extends ResourceObject
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
