<?php

namespace NckRtl\RouteMaker;

use Attribute;
use NckRtl\RouteMaker\Enums\HttpMethod;

#[Attribute]
class Route
{
    public function __construct(
        public HttpMethod $method = HttpMethod::GET,
        public ?string $uri = null,
        public ?string $name = null,
        public ?array $parameters = null,
        public array|string|null $middleware = null,
    ) {}
}
