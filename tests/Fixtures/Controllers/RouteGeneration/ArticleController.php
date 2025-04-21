<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Enums\HttpMethod;
use NckRtl\RouteMaker\Route;

class ArticleController extends Controller
{
    protected static string $routePrefix = 'articles';

    protected static array $routeMiddleware = ['auth', 'verified'];

    #[Route(parameters: ['article:slug'])]
    public function show(string $article): Response
    {
        return inertia('Article/Show', [
            'article' => $article,
        ]);
    }

    #[Route(method: HttpMethod::POST)]
    public function store(): Response
    {
        return inertia('Article/Store');
    }

    #[Route(method: HttpMethod::PUT, parameters: ['article:slug'])]
    public function update(string $article): Response
    {
        return inertia('Article/Update', [
            'article' => $article,
        ]);
    }

    #[Route(method: HttpMethod::PATCH, parameters: ['article:slug'])]
    public function edit(string $article): Response
    {
        return inertia('Article/Edit', [
            'article' => $article,
        ]);
    }

    #[Route(method: HttpMethod::DELETE, parameters: ['article:slug'])]
    public function destroy(string $article): Response
    {
        return inertia('Article/Destroy', [
            'article' => $article,
        ]);
    }
}