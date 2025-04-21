<?php

use NckRtl\RouteMaker\RouteMaker;

test('it generates correct route definitions', function () {
    RouteMaker::setControllerPath(
        __DIR__.'/Http/Controllers',
        'NckRtl\\RouteMaker\\Tests\\Http\\Controllers'
    );

    $routes = RouteMaker::generateRouteDefinitions();

    $expectedGetRoute = "Route::get('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'show'])->name('Controllers.ArticleController.show')->middleware(['auth', 'verified']);";
    $expectedPostRoute = "Route::post('/articles', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'store'])->name('Controllers.ArticleController.store')->middleware(['auth', 'verified']);";
    $expectedPutRoute = "Route::put('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'update'])->name('Controllers.ArticleController.update')->middleware(['auth', 'verified']);";
    $expectedPatchRoute = "Route::patch('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'edit'])->name('Controllers.ArticleController.edit')->middleware(['auth', 'verified']);";
    $expectedDeleteRoute = "Route::delete('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\ArticleController::class, 'destroy'])->name('Controllers.ArticleController.destroy')->middleware(['auth', 'verified']);";

    expect($routes)->toContain($expectedGetRoute);
    expect($routes)->toContain($expectedPostRoute);
    expect($routes)->toContain($expectedPutRoute);
    expect($routes)->toContain($expectedPatchRoute);
    expect($routes)->toContain($expectedDeleteRoute);
});

test('it uses controller name for route names and URIs when no prefix is defined', function () {
    $tempPath = __DIR__.'/Http/Controllers/temp';
    if (! is_dir($tempPath)) {
        mkdir($tempPath, 0777, true);
    }

    $controllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;

class HomeController extends Controller
{
    public function show(): Response
    {
        return inertia('Home');
    }

    public function index(): Response
    {
        return inertia('Home/Index');
    }
}
PHP;

    file_put_contents($tempPath.'/HomeController.php', $controllerContent);

    // Add a contact controller to test the show method URI
    $contactControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;

class ContactController extends Controller
{
    public function show(): Response
    {
        return inertia('Contact');
    }

    public function store(): Response
    {
        return inertia('Contact/Store');
    }
}
PHP;

    file_put_contents($tempPath.'/ContactController.php', $contactControllerContent);

    RouteMaker::setControllerPath(
        $tempPath,
        'NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp'
    );

    $routes = RouteMaker::generateRouteDefinitions();

    // Test home routes with new naming convention
    $expectedHomeShowRoute = "Route::get('/home', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\HomeController::class, 'show'])->name('Controllers.HomeController.show');";
    $expectedHomeIndexRoute = "Route::get('/home', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\HomeController::class, 'index'])->name('Controllers.HomeController.index');";

    // Test contact routes with new naming convention
    $expectedContactShowRoute = "Route::get('/contact', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\ContactController::class, 'show'])->name('Controllers.ContactController.show');";
    $expectedContactStoreRoute = "Route::post('/contact', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\ContactController::class, 'store'])->name('Controllers.ContactController.store');";

    expect($routes)->toContain($expectedHomeShowRoute);
    expect($routes)->toContain($expectedHomeIndexRoute);
    expect($routes)->toContain($expectedContactShowRoute);
    expect($routes)->toContain($expectedContactStoreRoute);

    // Clean up
    system('rm -rf '.escapeshellarg($tempPath));
});

test('it uses controller name for route names even when prefix is defined', function () {
    $tempPath = __DIR__.'/Http/Controllers/temp';
    if (! is_dir($tempPath)) {
        mkdir($tempPath, 0777, true);
    }

    $controllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;

class ArticleController extends Controller
{
    protected static string $routePrefix = 'articles';

    #[Route(parameters: ['article:slug'])]
    public function show(): Response
    {
        return inertia('Article/Show');
    }

    public function store(): Response
    {
        return inertia('Article/Store');
    }
}
PHP;

    file_put_contents($tempPath.'/ArticleController.php', $controllerContent);

    RouteMaker::setControllerPath(
        $tempPath,
        'NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp'
    );

    $routes = RouteMaker::generateRouteDefinitions();

    $expectedShowRoute = "Route::get('/articles/{article:slug}', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\ArticleController::class, 'show'])->name('Controllers.ArticleController.show');";
    $expectedStoreRoute = "Route::post('/articles', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\ArticleController::class, 'store'])->name('Controllers.ArticleController.store');";

    expect($routes)->toContain($expectedShowRoute);
    expect($routes)->toContain($expectedStoreRoute);

    // Clean up
    system('rm -rf '.escapeshellarg($tempPath));
});
