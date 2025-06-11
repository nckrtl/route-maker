<?php

namespace NckRtl\RouteMaker;

use NckRtl\RouteMaker\Enums\HttpMethod;

abstract class RouteAttribute
{
    public HttpMethod $method;

    public function __construct(
        public ?string $uri = null,
        public ?string $name = null,
        public ?array $parameters = null,
        public array|string|null $middleware = null,
    ) {}
}
