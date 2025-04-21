<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;
use NckRtl\RouteMaker\Enums\HttpMethod;

class MiddlewareTestController extends Controller
{
    protected static string $routePrefix = 'middleware-test';
    protected static array $routeMiddleware = ['controller-mw'];

    #[Route(method: HttpMethod::POST, middleware: 'method-mw')]
    public function store(): Response
    {
        return inertia('Middleware/Store');
    }

    #[Route(method: HttpMethod::GET, middleware: ['method-mw', 'another-mw'])]
    public function index(): Response
    {
        return inertia('Middleware/Index');
    }

    #[Route(method: HttpMethod::GET, uri: 'no-method-mw')]
    public function noMethodMiddleware(): Response
    {
        return inertia('Middleware/NoMethod');
    }

    #[Route(method: HttpMethod::PUT, middleware: ['controller-mw', 'unique-method-mw'])]
    public function update(): Response
    {
        return inertia('Middleware/Update');
    }
}