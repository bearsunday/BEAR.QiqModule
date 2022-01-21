<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Sunday\Extension\Transfer\TransferInterface;
use BEAR\Resource\ResourceObject;

class FakeHttpResponder implements TransferInterface
{
    public static $code;
    public static $headers = [];
    public static $content;

    public static function reset()
    {
        static::$headers = [];
        static::$content = null;
    }

    public function __invoke(ResourceObject $ro, array $server)
    {
        unset($server);
        $ro->toString();

        self::$content = $ro->view;
        self::$code = $ro->code;
        self::$headers = $ro->headers;
    }
}
