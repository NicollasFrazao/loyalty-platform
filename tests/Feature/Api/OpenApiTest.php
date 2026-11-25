<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * docs/api/openapi.json describes the customer API: every route under api/v1 is in it, and it
 * describes no route that does not exist.
 */

/**
 * The operations of the routes under api/v1, as "METHOD /path" with the document's base removed.
 *
 * @return list<string>
 */
function apiRoutes(): array
{
    $operations = [];

    foreach (Route::getRoutes()->getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'api/v1/')) {
            continue;
        }

        foreach (array_diff($route->methods(), ['HEAD']) as $method) {
            $operations[] = $method.' '.substr($route->uri(), strlen('api/v1'));
        }
    }

    sort($operations);

    return $operations;
}

/**
 * The paths of docs/api/openapi.json, each with its operations by lower-case method.
 *
 * @return array<string, array<string, mixed>>
 *
 * @throws JsonException when the document is not JSON.
 * @throws UnexpectedValueException when it has no paths.
 */
function openApiPaths(): array
{
    $document = json_decode((string) file_get_contents(base_path('docs/api/openapi.json')), true, flags: JSON_THROW_ON_ERROR);

    if (
        ! is_array($document)
        || ! is_array($document['paths'] ?? null)
    ) {
        throw new UnexpectedValueException('The OpenAPI document has no paths.');
    }

    $paths = [];

    foreach ($document['paths'] as $path => $item) {
        $paths[(string) $path] = [];

        foreach (is_array($item) ? $item : [] as $method => $operation) {
            $paths[(string) $path][(string) $method] = $operation;
        }
    }

    return $paths;
}

/**
 * The operations the document describes, as "METHOD /path".
 *
 * @return list<string>
 *
 * @throws JsonException when the document is not JSON.
 */
function documentedOperations(): array
{
    $operations = [];

    foreach (openApiPaths() as $path => $item) {
        foreach (array_intersect(array_keys($item), ['get', 'post', 'put', 'patch', 'delete']) as $method) {
            $operations[] = strtoupper($method).' '.$path;
        }
    }

    sort($operations);

    return $operations;
}

it('describes every route of the customer API, and nothing else', function (): void {
    expect(apiRoutes())->not->toBeEmpty()
        ->toBe(documentedOperations());
});

it('declares the bearer token on every route that asks for one', function (): void {
    $paths = openApiPaths();

    foreach (Route::getRoutes()->getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'api/v1/')) {
            continue;
        }

        $path = substr($route->uri(), strlen('api/v1'));
        $secured = in_array('auth:sanctum', $route->gatherMiddleware(), true);

        foreach (array_diff($route->methods(), ['HEAD']) as $method) {
            $operation = $paths[$path][strtolower($method)] ?? [];

            expect(is_array($operation) && isset($operation['security']))->toBe($secured, "{$method} {$path}");
        }
    }
});
