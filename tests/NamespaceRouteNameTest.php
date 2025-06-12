<?php

use Illuminate\Support\Facades\File;
use NckRtl\RouteMaker\RouteMaker;
use NckRtl\RouteMaker\Tests\Traits\TestFixtures;

uses(TestFixtures::class);

beforeEach(function () {
    $this->setUpFixtures();
});

afterEach(function () {
    $this->tearDownFixtures();
});

it('generates route names with full namespace path for nested controllers', function () {
    // Create a Settings subdirectory
    $settingsPath = $this->tempPath.'/Settings';
    File::makeDirectory($settingsPath, 0777, true);

    // Create a controller in the Settings subdirectory
    $profileControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp\Settings;

use NckRtl\RouteMaker\Get;

class ProfileController
{
    #[Get(uri: '/settings/profile', middleware: 'auth')]
    public function edit()
    {
        return 'edit profile';
    }
}
PHP;

    file_put_contents($settingsPath.'/ProfileController.php', $profileControllerContent);

    $this->setupRouteMaker();

    $routes = RouteMaker::generateRouteDefinitions();

    // Find the route for the ProfileController edit method
    $profileRoute = null;
    foreach ($routes as $route) {
        if (str_contains($route, 'ProfileController') && str_contains($route, 'edit')) {
            $profileRoute = $route;
            break;
        }
    }

    expect($profileRoute)->not->toBeNull();

    // The route name should include the full namespace path
    expect($profileRoute)->toContain("->name('Controllers.Settings.ProfileController.edit')");
});

it('generates route names with deeply nested namespace paths', function () {
    // Create a deeply nested directory structure
    $deepPath = $this->tempPath.'/Admin/Reports/Financial';
    File::makeDirectory($deepPath, 0777, true);

    // Create a controller in the deep directory
    $revenueControllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp\Admin\Reports\Financial;

use NckRtl\RouteMaker\Get;

class RevenueController
{
    #[Get]
    public function index()
    {
        return 'revenue report';
    }
    
    #[Get(name: 'custom.revenue.show')]
    public function show()
    {
        return 'show revenue';
    }
}
PHP;

    file_put_contents($deepPath.'/RevenueController.php', $revenueControllerContent);

    $this->setupRouteMaker();

    $routes = RouteMaker::generateRouteDefinitions();

    // Find the routes for the RevenueController
    $indexRoute = null;
    $showRoute = null;
    foreach ($routes as $route) {
        if (str_contains($route, 'RevenueController') && str_contains($route, 'index')) {
            $indexRoute = $route;
        }
        if (str_contains($route, 'RevenueController') && str_contains($route, 'show')) {
            $showRoute = $route;
        }
    }

    expect($indexRoute)->not->toBeNull();
    expect($showRoute)->not->toBeNull();

    // The index route name should include the full namespace path
    expect($indexRoute)->toContain("->name('Controllers.Admin.Reports.Financial.RevenueController.index')");

    // The show route should use the custom name
    expect($showRoute)->toContain("->name('custom.revenue.show')");
});
