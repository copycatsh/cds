<?php

declare(strict_types=1);

use App\Bootstrap;
use App\Exception\ConflictException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Http\Request;
use App\Http\Response;
use FastRoute\Dispatcher;

require_once __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$request = Request::fromGlobals();

try {
    $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($bootstrap) {
        $clients = $bootstrap->getClientController();
        $orders = $bootstrap->getOrderController();

        $r->addRoute('GET', '/clients', [$clients, 'index']);
        $r->addRoute('POST', '/clients', [$clients, 'create']);
        $r->addRoute('GET', '/clients/{id:\d+}', [$clients, 'show']);
        $r->addRoute('DELETE', '/clients/{id:\d+}', [$clients, 'delete']);

        $r->addRoute('GET', '/orders', [$orders, 'index']);
        $r->addRoute('POST', '/orders', [$orders, 'create']);
        $r->addRoute('GET', '/orders/{id:\d+}', [$orders, 'show']);
    });

    $routeInfo = $dispatcher->dispatch($request->method, $request->uri);

    $response = match ($routeInfo[0]) {
        Dispatcher::NOT_FOUND => Response::error('not_found', 'Route not found', 404),
        Dispatcher::METHOD_NOT_ALLOWED => Response::error('method_not_allowed', 'Method not allowed', 405),
        Dispatcher::FOUND => (function () use ($routeInfo, $request): Response {
            [, $handler, $vars] = $routeInfo;
            $request->setRouteParams($vars);

            return $handler($request);
        })(),
    };
} catch (ValidationException $e) {
    $response = Response::error('validation_error', $e->getMessage(), 422);
} catch (NotFoundException $e) {
    $response = Response::error('not_found', $e->getMessage(), 404);
} catch (ConflictException $e) {
    $response = Response::error('conflict', $e->getMessage(), 409);
} catch (Throwable $e) {
    $response = Response::error('internal_error', 'Internal server error', 500);
}

$response->send();
