<?php

use NckRtl\RouteMaker\RouteMaker;
use NckRtl\RouteMaker\Tests\Traits\TestFixtures;
use Illuminate\Support\Facades\File;

uses(TestFixtures::class);

beforeEach(function () {
    $this->setUpFixtures();
});

afterEach(function () {
    $this->tearDownFixtures();
});

test('it discovers controllers in subdirectories', function () {
    // Create a subdirectory structure with controllers
    $authPath = $this->tempPath.'/Auth';
    File::makeDirectory($authPath, 0777, true);
    
    // Create a controller in the root directory
    $homeControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use NckRtl\RouteMaker\Get;

class HomeController
{
    #[Get]
    public function index()
    {
        return 'home';
    }
}
PHP;
    
    file_put_contents($this->tempPath.'/HomeController.php', $homeControllerContent);
    
    // Create a controller in the Auth subdirectory
    $loginControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp\Auth;

use NckRtl\RouteMaker\Get;
use NckRtl\RouteMaker\Post;

class LoginController
{
    #[Get(uri: '/login')]
    public function showLoginForm()
    {
        return 'login form';
    }
    
    #[Post(uri: '/login')]
    public function login()
    {
        return 'processing login';
    }
}
PHP;
    
    file_put_contents($authPath.'/LoginController.php', $loginControllerContent);
    
    $this->setupRouteMaker();
    
    $routes = RouteMaker::generateRouteDefinitions();
    $routesString = implode("\n", $routes);
    
    // Check that both controllers are discovered
    expect($routesString)->toContain('HomeController');
    expect($routesString)->toContain('Auth\\LoginController');
    
    // Check specific routes (note: HomeController generates /home not / by default)
    expect($routesString)->toContain("Route::get('/home', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\HomeController::class, 'index'])");
    expect($routesString)->toContain("Route::get('/login', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\Auth\\LoginController::class, 'showLoginForm'])");
    expect($routesString)->toContain("Route::post('/login', [\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\Auth\\LoginController::class, 'login'])");
});

test('it generates correct fully qualified class names for controllers in subdirectories', function () {
    // Create a Settings subdirectory
    $settingsPath = $this->tempPath.'/Settings';
    File::makeDirectory($settingsPath, 0777, true);
    
    // Create a controller in the Settings subdirectory
    $profileControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp\Settings;

use NckRtl\RouteMaker\Get;
use NckRtl\RouteMaker\Put;

class ProfileController
{
    protected static array $routeMiddleware = ['auth'];
    
    #[Get]
    public function edit()
    {
        return 'edit profile';
    }
    
    #[Put]
    public function update()
    {
        return 'update profile';
    }
}
PHP;
    
    file_put_contents($settingsPath.'/ProfileController.php', $profileControllerContent);
    
    $this->setupRouteMaker();
    
    $routes = RouteMaker::generateRouteDefinitions();
    
    // Find routes for the ProfileController
    $profileRoutes = array_filter($routes, function ($route) {
        return str_contains($route, 'ProfileController');
    });
    
    expect($profileRoutes)->not->toBeEmpty();
    
    foreach ($profileRoutes as $route) {
        // Check that the fully qualified class name includes the subdirectory
        expect($route)->toContain('\\NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp\\Settings\\ProfileController');
        // Check that middleware is applied
        expect($route)->toContain("->middleware('auth')");
    }
});

test('it handles deeply nested controller directories', function () {
    // Create a deeply nested controller structure
    $deepPath = $this->tempPath.'/Admin/Reports/Financial';
    File::makeDirectory($deepPath, 0777, true);
    
    // Create a controller in the deep directory
    $revenueControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp\Admin\Reports\Financial;

use NckRtl\RouteMaker\Get;

class RevenueController
{
    protected static string $routePrefix = 'admin/reports/financial/revenue';
    
    #[Get]
    public function index()
    {
        return 'revenue report';
    }
    
    #[Get(uri: 'monthly')]
    public function monthly()
    {
        return 'monthly revenue';
    }
}
PHP;
    
    file_put_contents($deepPath.'/RevenueController.php', $revenueControllerContent);
    
    $this->setupRouteMaker();
    
    $routes = RouteMaker::generateRouteDefinitions();
    $routesString = implode("\n", $routes);
    
    // Check that the deeply nested controller is discovered
    expect($routesString)->toContain('Admin\\Reports\\Financial\\RevenueController');
    expect($routesString)->toContain('/admin/reports/financial/revenue');
    expect($routesString)->toContain("Route::get('/monthly'");
});