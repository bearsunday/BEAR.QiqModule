<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\Exception\ResourceNotFoundException as NotFound;
use BEAR\Resource\Exception\ServerErrorException as ServerError;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

use function dirname;
use function serialize;
use function unserialize;

class QiqErrorPageHandlerTest extends TestCase
{
    protected QiqErrorModule $qiqErrorModule;
    private QiqErrorHandler $handler;

    protected function setUp(): void
    {
        $qiqTemplateDir = dirname(__DIR__) . '/tests/Fake/templates';
        $qiqErrorViewName = 'Error';

        $this->qiqErrorModule = new QiqErrorModule($qiqErrorViewName);

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

    public function testSleep(): void
    {
        $errorPage = unserialize(serialize(new QiqErrorPage()));
        $this->assertInstanceOf(QiqErrorPage::class, $errorPage);
    }
}
