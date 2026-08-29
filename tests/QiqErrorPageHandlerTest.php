<?php

declare(strict_types=1);

namespace BEAR\QiqModule;

use BEAR\Resource\Exception\ResourceNotFoundException as NotFound;
use BEAR\Resource\Exception\ServerErrorException as ServerError;
use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ray\Di\Injector;

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
        $module = new QiqModule($qiqTemplateDir, $this->qiqErrorModule);

        $errorPage = new QiqErrorPage();
        $errorPage->setRenderer((new Injector($module))->getInstance(RenderInterface::class, 'error_page'));

        $this->handler = new QiqErrorHandler(
            $errorPage,
            new FakeHttpResponder(),
            new NullLogger(),
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

    /** @depends testHandle */
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

    /** The page must get the 'error_page' renderer from DI, not the default RenderInterface binding */
    public function testInjectedErrorPageRendersThroughTheErrorPageRenderer(): void
    {
        $injector = new Injector(
            new QiqModule(dirname(__DIR__) . '/tests/Fake/templates', new QiqErrorModule('Error')),
            dirname(__DIR__) . '/tests/tmp',
        );
        $handler = new QiqErrorHandler(
            $injector->getInstance(QiqErrorPage::class),
            new FakeHttpResponder(),
            new NullLogger(),
        );

        $request = new RouterMatch();
        $request->method = 'get';
        $request->path = '/';
        $request->query = [];
        FakeHttpResponder::reset();
        $handler->handle(new ServerError(), $request)->transfer();

        $this->assertStringStartsWith('code: 503 message: Service Unavailable', (string) FakeHttpResponder::$content);
    }
}
