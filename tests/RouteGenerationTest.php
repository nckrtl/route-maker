<?php

use NckRtl\WayfinderRoutes\WayfinderRoutes;

test('it generates correct route definitions', function () {
    WayfinderRoutes::setControllerPath(
        __DIR__.'/Http/Controllers',
        'NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers'
    );

    $routes = WayfinderRoutes::generateRouteDefinitions();

    $expectedGetRoute = "Route::get('/articles/{article:slug}', [\\NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers\\ArticleController::class, 'show'])->name('article.show');";
    $expectedPostRoute = "Route::post('/articles', [\\NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers\\ArticleController::class, 'store'])->name('article.store');";
    $expectedPutRoute = "Route::put('/articles/{article:slug}', [\\NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers\\ArticleController::class, 'update'])->name('article.update');";
    $expectedPatchRoute = "Route::patch('/articles/{article:slug}', [\\NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers\\ArticleController::class, 'edit'])->name('article.edit');";
    $expectedDeleteRoute = "Route::delete('/articles/{article:slug}', [\\NckRtl\\WayfinderRoutes\\Tests\\Http\\Controllers\\ArticleController::class, 'destroy'])->name('article.destroy');";

    expect($routes)->toContain($expectedGetRoute);
    expect($routes)->toContain($expectedPostRoute);
    expect($routes)->toContain($expectedPutRoute);
    expect($routes)->toContain($expectedPatchRoute);
    expect($routes)->toContain($expectedDeleteRoute);
});
