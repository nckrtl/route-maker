<?php

namespace NckRtl\WayfinderRoutes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use NckRtl\WayfinderRoutes\WayfinderRoutes;

class WayfinderRoutesCommand extends Command
{
    public $signature = 'wayfinder:routes';

    public $description = 'My command';

    public function handle(): int
    {
        $filePath = base_path('routes/wayfinder.php');

        // Ensure the routes directory exists
        if (! File::exists(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Generate routes
        $routes = WayfinderRoutes::generateRouteDefinitions();

        // Compose the full file content with opening tag + use statement
        $content = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n".implode("\n", $routes)."\n";

        // Save the file
        File::put($filePath, $content);

        $this->info('Wayfinder routes dumped successfully to routes/wayfinder.php');

        return self::SUCCESS;
    }
}
