<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\QiqModule\Annotation\Layout;
use BEAR\Resource\ResourceObject;

final class FakeBrowse extends ResourceObject
{
    /**
     * @param array<string, int|string> $data
     */
    #[Layout('/layout/custom')]
    public function onGet(array $data): ResourceObject
    {
        $this->body = $data;

        return $this;
    }
}
