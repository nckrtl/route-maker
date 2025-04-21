# Route Maker

This Laravel packages lets you generate a routes file based on your public controller methods. This package works particularly well with Laravel Wayfinder, as it allows you to reference controller methods instead of just routes. Based on the method signature in your controllers we could generate a routes file, automating route management entirely.

## Installation

You can install the package via composer:

```bash
composer require nckrtl/route-maker
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="route-maker-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="route-maker-config"
```

This is the contents of the published config file:

```php
return [
    'method_defaults' => [
        'GET' => ['index', 'show'],
        'POST' => ['store'],
        'PUT' => ['update'],
        'DELETE' => ['destroy'],,
    ],
];
```

## Usage

Update your vite config to include an additional run command:

```ts
import { run } from "vite-plugin-run";

export default defineConfig({
    plugins: [
        run([
            {
                name: "route-maker",
                run: ["php", "artisan", "route-maker:make"],
                pattern: ["app/**/Http/**/*.php"],
            },
        ]),
    ],
});
```

Next, update your main routes file to include the generated routes with:

```php
use NckRtl\RouteMaker\Facades\RouteMaker;

RouteMaker::routes();
```

Now you're all set. Running vite dev should nog generate the routes based on your controller methods. On file change of any controller the routes file will be regenerated.

### Route definition structure

The way routes are generated are pretty opionated. The naming convention of routes is inspired by how Laravel Wayfinder exposes routes/actions. For this controller:

```php
<?php

namespace App\Http\Controllers;

class ContactController extends Controller
{
    public function show(): \Inertia\Response
    {
        return inertia('Contact');
    }
}
```

The generated route definition will look like:

```php
Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'show'])->name('Controllers.ContactController.show');
```

### Setting route parameters and other properties.

To influence the route that is being generated you can you the `Route` attribute. For example you can define a route parameter like so:

```php
use NckRtl\RouteMaker\Route;

...

#[Route(parameters: ['article:slug'])]
public function show(Article $article): \Inertia\Response
{
    return inertia('Article/Show', [
        'article' => $article->data->forDisplay(),
    ]);
}
```

Other route properties are also supported like `middleware`. Besides setting middelware on specific methods you can also set them at the controller level, just as a prefix:

```php
class ArticleController extends Controller
{
    protected static string $routePrefix = 'articles';
    protected static string $routeMiddleware = 'auth:verified';

    ...
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Feel free to contribute. Make sure to add/update tests for new or improved features.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [nckrtl](https://github.com/nckrtl)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
