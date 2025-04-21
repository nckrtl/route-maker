<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;
use NckRtl\RouteMaker\Enums\HttpMethod;

class MethodDefaultsTestController extends Controller
{
    protected static string $routePrefix = 'test';

    // Method with no explicit HTTP method - should use default GET for 'index'
    public function index(): Response
    {
        return inertia('Test/Index');
    }

    // Method with no explicit HTTP method - should use default GET for 'show'
    public function show(): Response
    {
        return inertia('Test/Show');
    }

    // Method with no explicit HTTP method - should use default POST for 'store'
    public function store(): Response
    {
        return inertia('Test/Store');
    }

    // Method with no explicit HTTP method - should use default PUT/PATCH for 'update'
    public function update(): Response
    {
        return inertia('Test/Update');
    }

    // Method with no explicit HTTP method - should use default DELETE for 'destroy'
    public function destroy(): Response
    {
        return inertia('Test/Destroy');
    }

    // Method with explicit HTTP method - should override default
    #[Route(method: HttpMethod::GET)]
    public function store_override(): Response
    {
        return inertia('Test/StoreOverride');
    }
}