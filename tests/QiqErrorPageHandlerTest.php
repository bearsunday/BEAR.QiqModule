<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\Exception\ServerErrorException as ServerError;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ray\Di\Exception\NotFound;

use function dirname;

class QiqErrorPageHandlerTest extends TestCase
{
    protected QiqModule $qiqModule;
    private QiqErrorHandler $handler;

    protected function setUp(): void
    {
        $qiqTemplateDir = dirname(__DIR__) . '/tests/Fake/templates';
        $qiqErrorViewName = 'Error';

        $this->qiqModule = new QiqModule($qiqTemplateDir, $qiqErrorViewName);

        $errorPage = new QiqErrorPage();
        $errorPage->setRenderer(new QiqErrorPageRenderer($qiqTemplateDir, $qiqErrorViewName));

        $this->handler = new QiqErrorHandler(
            $errorPage,
            new FakeHttpResponder(),
            new NullLogger()
        );

        parent::setUp();
    }

    public function testHandle(): ErrorInterface
    {
        $request = new RouterMatch();
        $request->method = 'get';
        $request->path = '/';
        $request->query = [];
        $handler = $this->handler->handle(new NotFound(), $request);
        $this->assertInstanceOf(QiqErrorHandler::class, $handler);

        return $handler;
    }

    /**
     * @depends testHandle
     */
    public function testTransfer(): void
    {
        $request = new RouterMatch();
        $request->method = 'get';
        $request->path = '/';
        $request->query = [];
        $handler = $this->handler->handle(new ServerError(), $request);
        $handler->transfer();

        $this->assertSame(503, FakeHttpResponder::$code);
        $this->assertSame('text/html; charset=utf-8', FakeHttpResponder::$headers['content-type']);
        $this->assertStringStartsWith('code: 503 message: Service Unavailable', FakeHttpResponder::$content);
    }
}
