<?php

namespace NckRtl\RouteMaker;

use Illuminate\Support\Str;
use NckRtl\RouteMaker\Enums\HttpMethod;

class RouteMaker
{
    protected static ?string $controllerPath = null;

    protected static ?string $controllerNamespace = null;

    public static function routes()
    {
        if (file_exists(base_path('routes/route-maker.php'))) {
            require base_path('routes/route-maker.php');
        }
    }

    public static function setControllerPath(?string $path, ?string $namespace = null): void
    {
        self::$controllerPath = $path ?? app_path('Http/Controllers');
        self::$controllerNamespace = $namespace ?? 'App\\Http\\Controllers';
    }

    protected static function getMethodDefault(string $methodName): ?HttpMethod
    {
        $defaults = config('route-maker.method_defaults', []);

        foreach ($defaults as $method => $methodNames) {
            if (in_array($methodName, $methodNames)) {
                return HttpMethod::from($method);
            }
        }

        return null;
    }

    public static function generateRouteDefinitions(): array
    {
        $groupedRoutes = [];

        $controllerPath = self::$controllerPath ?? app_path('Http/Controllers');
        $namespace = self::$controllerNamespace ?? 'App\\Http\\Controllers';

        $files = (new \Symfony\Component\Finder\Finder)->files()->in($controllerPath)->name('*Controller.php');

        foreach ($files as $file) {
            $relativePath = str_replace([$controllerPath.'/', '.php'], ['', ''], $file->getRealPath());
            $class = $namespace.'\\'.str_replace('/', '\\', $relativePath);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            $routePrefix = $reflection->hasProperty('routePrefix')
                ? $reflection->getStaticPropertyValue('routePrefix')
                : null;

            $controllerMiddleware = $reflection->hasProperty('routeMiddleware')
                ? $reflection->getStaticPropertyValue('routeMiddleware')
                : [];

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->class !== $class) {
                    continue;
                }

                $returnType = $method->getReturnType();
                if (! $returnType instanceof \ReflectionNamedType ||
                    $returnType->getName() !== \Inertia\Response::class) {
                    continue;
                }

                $attribute = collect($method->getAttributes(Route::class))->first();
                $routeAttr = $attribute ? $attribute->newInstance() : null;

                $routeMiddleware = $routeAttr?->middleware ?? [];
                if (is_string($routeMiddleware)) {
                    $routeMiddleware = [$routeMiddleware];
                }

                $combinedMiddleware = array_unique(array_merge($controllerMiddleware, $routeMiddleware));

                // Use the explicit method from the attribute if set, otherwise use the default based on method name
                $httpMethod = $routeAttr?->method ?? self::getMethodDefault($method->name) ?? HttpMethod::GET;
                $httpMethod = strtolower($httpMethod->value);

                $uri = self::generateUri($routePrefix, $routeAttr?->uri, $routeAttr?->parameters);
                $routeName = self::generateRouteName($routePrefix, $method->name, $routeAttr?->name);

                $escapedClass = '\\'.ltrim($class, '\\');
                $definition = sprintf(
                    "Route::%s('%s', [%s::class, '%s'])->name('%s')",
                    $httpMethod,
                    $uri,
                    $escapedClass,
                    $method->name,
                    $routeName
                );

                if (! empty($combinedMiddleware)) {
                    $definition .= sprintf('->middleware(%s)', self::formatMiddleware($combinedMiddleware));
                }

                $definition .= ';';

                $groupKey = $routePrefix ?? '/';
                $groupedRoutes[$groupKey][] = $definition;
            }
        }

        // Flatten the grouped definitions into a single array, with group comments
        $flattened = [];
        foreach ($groupedRoutes as $prefix => $routes) {
            $flattened[] = '// /'.trim($prefix, '/');
            foreach ($routes as $definition) {
                $flattened[] = $definition;
            }
            $flattened[] = ''; // Add a blank line between groups
        }

        return $flattened;
    }

    protected static function formatMiddleware(array $middleware): string
    {
        if (count($middleware) === 1) {
            return "'".$middleware[0]."'";
        }

        return '[\''.implode("', '", $middleware).'\']';
    }

    protected static function generateUri(?string $prefix, ?string $customUri, ?array $parameters): string
    {
        if ($customUri) {
            return '/'.ltrim($customUri, '/');
        }

        $uri = $prefix ? '/'.trim($prefix, '/') : '/';

        if ($parameters) {
            $wrappedParams = array_map(fn ($param) => '{'.$param.'}', $parameters);
            $uri = rtrim($uri, '/').'/'.implode('/', $wrappedParams);
        }

        return $uri === '' ? '/' : $uri;
    }

    protected static function generateRouteName(?string $prefix, string $methodName, ?string $customName): string
    {
        if ($customName) {
            return $customName;
        }

        if ($prefix) {
            return Str::singular($prefix).'.'.$methodName;
        }

        return $methodName === 'show' ? 'home' : $methodName;
    }
}
