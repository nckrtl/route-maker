# Changelog

All notable changes to `route-maker` will be documented in this file.

## v0.3.0 - 2024-04-21

### Added

-   Smart URI generation for RESTful controller methods
-   Resource methods like show, edit, update, and destroy now automatically append {id} parameter
-   Custom non-standard methods append the method name to the URI
-   Test coverage for multiple methods in controllers
-   Improved controller method discovery and handling

### Fixed

-   Issue with duplicate URIs for controllers with multiple methods
-   Route conflicts when methods use the same HTTP verb
