<?php

namespace NckRtl\RouteMaker;

use Attribute;
use NckRtl\RouteMaker\Enums\HttpMethod;

#[Attribute]
class Patch extends RouteAttribute
{
    public function __construct(
        public ?string $uri = null,
        public ?string $name = null,
        public ?array $parameters = null,
        public array|string|null $middleware = null,
    ) {
        $this->method = HttpMethod::PATCH;
        parent::__construct(
            $uri,
            $name,
            $parameters,
            $middleware,
        );
    }
}
