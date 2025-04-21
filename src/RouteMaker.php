<?php

namespace NckRtl\RouteMaker;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NckRtl\RouteMaker\Enums\HttpMethod;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class RouteMaker
{
    private static ?string $controllerPath = null;

    private static ?string $controllerNamespace = null;

    /**
     * Load routes from the route-maker.php file.
     */
    public static function routes(): void
    {
        $routeFile = base_path('routes/route-maker.php');

        if (file_exists($routeFile)) {
            try {
                require $routeFile;
            } catch (\Throwable $e) {
                Log::error("Failed to load route-maker.php: {$e->getMessage()}");
            }
        }
    }

    /**
     * Set the controller path and namespace.
     *
     * @param  string|null  $path  The controller path (defaults to app_path('Http/Controllers') if null)
     * @param  string|null  $namespace  The controller namespace (defaults to 'App\\Http\\Controllers' if null)
     */
    public static function setControllerPath(?string $path, ?string $namespace = null): void
    {
        self::$controllerPath = $path ?? app_path('Http/Controllers');
        self::$controllerNamespace = $namespace ?? 'App\\Http\\Controllers';
    }

    /**
     * Get the HTTP method based on the method name from configuration.
     *
     * @param  string  $methodName  The controller method name
     * @return HttpMethod|null The HTTP method or null if no match found
     */
    private static function getMethodDefault(string $methodName): ?HttpMethod
    {
        $defaults = config('route-maker.method_defaults', []);

        foreach ($defaults as $method => $methodNames) {
            if (in_array($methodName, $methodNames, true)) {
                try {
                    return HttpMethod::from($method);
                } catch (\ValueError $e) {
                    Log::warning("Invalid HTTP method '{$method}' in configuration");
                }
            }
        }

        return null;
    }

    /**
     * Get the kebab-case name of a controller without the "Controller" suffix.
     *
     * @param  string  $controllerName  The controller name
     * @return string The kebab-cased name
     */
    private static function getControllerBaseName(string $controllerName): string
    {
        return Str::kebab(str_replace('Controller', '', $controllerName));
    }

    /**
     * Generate route definitions for all controllers.
     *
     * @return array<string> Array of route definition strings
     */
    public static function generateRouteDefinitions(): array
    {
        $cacheKey = 'route-maker.definitions';

        // Skip caching in test environment
        if (app()->environment('production') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $groupedRoutes = [];

        $controllerPath = self::$controllerPath ?? app_path('Http/Controllers');
        $namespace = self::$controllerNamespace ?? 'App\\Http\\Controllers';

        try {
            $files = (new Finder)->files()->in($controllerPath)->name('*Controller.php');
        } catch (DirectoryNotFoundException $e) {
            Log::error("Controller directory not found: {$controllerPath}");

            return [];
        }

        foreach ($files as $file) {
            $relativePath = str_replace([$controllerPath.'/', '.php'], ['', ''], $file->getRealPath());
            $class = $namespace.'\\'.str_replace('/', '\\', $relativePath);

            if (! class_exists($class)) {
                continue;
            }

            self::processControllerClass($class, $groupedRoutes);
        }

        // Flatten the grouped definitions into a single array, with group comments
        $flattened = self::flattenGroupedRoutes($groupedRoutes);

        // Cache the result in production
        if (app()->environment('production')) {
            Cache::put($cacheKey, $flattened, now()->addMinutes(60));
        }

        return $flattened;
    }

    /**
     * Process a controller class to extract route definitions.
     *
     * @param  string  $class  The fully qualified controller class name
     * @param  array<string, array<string>>  &$groupedRoutes  Reference to the grouped routes array
     */
    private static function processControllerClass(string $class, array &$groupedRoutes): void
    {
        try {
            $reflection = new ReflectionClass($class);

            $routePrefix = null;
            if ($reflection->hasProperty('routePrefix')) {
                $routePrefix = $reflection->getStaticPropertyValue('routePrefix');
            }

            $controllerMiddleware = [];
            if ($reflection->hasProperty('routeMiddleware')) {
                $middlewareValue = $reflection->getStaticPropertyValue('routeMiddleware');
                $controllerMiddleware = is_array($middlewareValue) ? $middlewareValue : [$middlewareValue];
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->class !== $class) {
                    continue;
                }

                self::processControllerMethod($method, $reflection, $class, $controllerMiddleware, $routePrefix, $groupedRoutes);
            }
        } catch (ReflectionException $e) {
            Log::error("Failed to reflect class {$class}: {$e->getMessage()}");
        }
    }

    /**
     * Process a controller method to extract route definitions.
     *
     * @param  ReflectionMethod  $method  The reflection method
     * @param  ReflectionClass  $reflection  The reflection class
     * @param  string  $class  The fully qualified controller class name
     * @param  array<string>  $controllerMiddleware  The controller middleware
     * @param  string|null  $routePrefix  The route prefix
     * @param  array<string, array<string>>  &$groupedRoutes  Reference to the grouped routes array
     */
    private static function processControllerMethod(
        ReflectionMethod $method,
        ReflectionClass $reflection,
        string $class,
        array $controllerMiddleware,
        ?string $routePrefix,
        array &$groupedRoutes
    ): void {
        $attributes = $method->getAttributes(Route::class);
        $routeAttr = ! empty($attributes) ? $attributes[0]->newInstance() : null;

        // Extract middleware from route attribute
        $routeMiddleware = [];
        if ($routeAttr && $routeAttr->middleware !== null) {
            $routeMiddleware = is_string($routeAttr->middleware) ? [$routeAttr->middleware] : $routeAttr->middleware;
        }

        // Combine middleware, removing duplicates
        $combinedMiddleware = array_values(array_unique(array_merge($controllerMiddleware, $routeMiddleware)));

        // Determine HTTP method (from attribute, method defaults, or fallback to GET)
        $httpMethod = $routeAttr ? $routeAttr->method : (self::getMethodDefault($method->name) ?? HttpMethod::GET);
        $httpMethodValue = strtolower($httpMethod->value);

        // Generate URI and route name
        $uri = self::generateUri($routePrefix, $routeAttr?->uri, $routeAttr?->parameters, $reflection->getShortName(), $method->name);
        $routeName = self::generateRouteName($method->name, $routeAttr?->name, $reflection->getShortName());

        // Build the route definition
        $escapedClass = '\\'.ltrim($class, '\\');
        $definition = self::buildRouteDefinition(
            $httpMethodValue,
            $uri,
            $escapedClass,
            $method->name,
            $routeName,
            $combinedMiddleware
        );

        // Group routes by prefix for organization
        $groupKey = $routePrefix ?? '/';
        $groupedRoutes[$groupKey][] = $definition;
    }

    /**
     * Build a route definition string.
     *
     * @param  string  $httpMethod  The HTTP method
     * @param  string  $uri  The route URI
     * @param  string  $class  The controller class
     * @param  string  $methodName  The controller method name
     * @param  string  $routeName  The route name
     * @param  array<string>  $middleware  The middleware list
     * @return string The route definition
     */
    private static function buildRouteDefinition(
        string $httpMethod,
        string $uri,
        string $class,
        string $methodName,
        string $routeName,
        array $middleware
    ): string {
        $definition = sprintf(
            "Route::%s('%s', [%s::class, '%s'])->name('%s')",
            $httpMethod,
            $uri,
            $class,
            $methodName,
            $routeName
        );

        if (! empty($middleware)) {
            $definition .= sprintf('->middleware(%s)', self::formatMiddleware($middleware));
        }

        return $definition.';';
    }

    /**
     * Format middleware array into a string representation.
     *
     * @param  array<string>  $middleware  The middleware array
     * @return string Formatted middleware string
     */
    private static function formatMiddleware(array $middleware): string
    {
        if (count($middleware) === 1) {
            return "'".$middleware[0]."'";
        }

        return '[\''.implode("', '", $middleware).'\']';
    }

    /**
     * Generate URI for a route.
     *
     * @param  string|null  $prefix  The route prefix
     * @param  string|null  $customUri  Custom URI from route attribute
     * @param  array<string>|null  $parameters  Route parameters
     * @param  string  $controllerName  Controller name
     * @param  string  $methodName  Method name
     * @return string The generated URI
     */
    private static function generateUri(
        ?string $prefix,
        ?string $customUri,
        ?array $parameters,
        string $controllerName,
        string $methodName
    ): string {
        // If custom URI is provided, use it
        if ($customUri) {
            return '/'.ltrim($customUri, '/');
        }

        // Base URI from prefix or controller name
        if ($prefix) {
            $uri = '/'.trim($prefix, '/');
        } else {
            $baseUri = self::getControllerBaseName($controllerName);
            $uri = '/'.$baseUri;
        }

        // Add parameters if present
        if ($parameters) {
            $wrappedParams = array_map(fn ($param) => '{'.$param.'}', $parameters);
            $uri = rtrim($uri, '/').'/'.implode('/', $wrappedParams);
        }

        // Ensure the URI is properly formatted
        return trim($uri, '/') === '' ? '/' : $uri;
    }

    /**
     * Generate route name for a method.
     *
     * @param  string  $methodName  The method name
     * @param  string|null  $customName  Custom route name from attribute
     * @param  string  $controllerName  Controller name
     * @return string The generated route name
     */
    private static function generateRouteName(string $methodName, ?string $customName, string $controllerName): string
    {
        if ($customName) {
            return $customName;
        }

        // Always use Controllers.{ControllerName}.{method} format unless a custom name is provided
        return sprintf('Controllers.%s.%s', $controllerName, $methodName);
    }

    /**
     * Flatten grouped routes into a single array with comments.
     *
     * @param  array<string, array<string>>  $groupedRoutes  The grouped routes
     * @return array<string> Flattened route definitions
     */
    private static function flattenGroupedRoutes(array $groupedRoutes): array
    {
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
}
