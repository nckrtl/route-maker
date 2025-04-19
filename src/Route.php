<?php

namespace NckRtl\WayfinderRoutes;

use Attribute;
use NckRtl\WayfinderRoutes\Enums\HttpMethod;

#[Attribute]
class Route
{
    public function __construct(
        public HttpMethod $method = HttpMethod::GET,
        public ?string $uri = null,
        public ?string $name = null,
        public ?array $parameters = null
    ) {}
}
