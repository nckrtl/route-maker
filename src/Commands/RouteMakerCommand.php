<?php

namespace NckRtl\RouteMaker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use NckRtl\RouteMaker\RouteMaker;

class RouteMakerCommand extends Command
{
    public $signature = 'route-maker:make';

    public $description = 'Generate routes for the application';

    public function handle(): int
    {
        $filePath = base_path('routes/route-maker.php');

        // Ensure the routes directory exists
        if (! File::exists(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Generate routes
        $routes = RouteMaker::generateRouteDefinitions();

        // Compose the full file content with opening tag + use statement
        $content = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n".implode("\n", $routes)."\n";

        // Save the file
        File::put($filePath, $content);

        $this->info('Wayfinder routes dumped successfully to routes/route-maker.php');

        return self::SUCCESS;
    }
}
