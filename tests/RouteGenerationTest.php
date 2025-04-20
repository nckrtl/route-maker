<?php

use NckRtl\RouteMaker\RouteMaker;

test('it generates correct route definitions', function () {
    RouteMaker::setControllerPath(
        __DIR__.'/Http/Controllers',
        'NckRtl\\RouteMaker\\Tests\\Http\\Controllers'
    );

    $routes = RouteMaker::generateRouteDefinitions();

    $expectedGetRoute = "Route::get('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'show'])->name('article.show')->middleware(['auth', 'verified']);";
    $expectedPostRoute = "Route::post('/articles', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'store'])->name('article.store')->middleware(['auth', 'verified']);";
    $expectedPutRoute = "Route::put('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'update'])->name('article.update')->middleware(['auth', 'verified']);";
    $expectedPatchRoute = "Route::patch('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'edit'])->name('article.edit')->middleware(['auth', 'verified']);";
    $expectedDeleteRoute = "Route::delete('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'destroy'])->name('article.destroy')->middleware(['auth', 'verified']);";

    expect($routes)->toContain($expectedGetRoute);
    expect($routes)->toContain($expectedPostRoute);
    expect($routes)->toContain($expectedPutRoute);
    expect($routes)->toContain($expectedPatchRoute);
    expect($routes)->toContain($expectedDeleteRoute);
});
