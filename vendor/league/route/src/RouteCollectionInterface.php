<?php

declare(strict_types=1);

namespace League\Route;

use Psr\Http\Server\RequestHandlerInterface;

interface RouteCollectionInterface
{
    public function delete(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function get(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function head(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function map(
        string|array $method,
        string $path,
        callable|array|string|RequestHandlerInterface $handler
    ): Route;
    public function options(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function patch(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function post(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
    public function put(string $path, callable|array|string|RequestHandlerInterface $handler): Route;
}
