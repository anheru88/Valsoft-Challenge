<?php

declare(strict_types=1);

use App\Library\Domains\Users\Models\User;
use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

/**
 * The document as it is written to disk. The round trip matters: the generator
 * uses stdClass for empty objects, which only becomes comparable to the
 * committed file once both have been through JSON.
 */
function generatedDocument(): array
{
    $document = app(Generator::class)(Scramble::getGeneratorConfig('default'));

    return json_decode((string) json_encode($document), associative: true);
}

/**
 * The committed spec is the published contract (API specification 11). This is
 * the breaking-change detector RFC 13 asks for: a change to a route, a
 * FormRequest or a Resource that is not reflected in openapi.json fails here,
 * where it is cheap, rather than at a client.
 */
it('keeps the committed specification in sync with the code', function () {
    $committed = json_decode((string) file_get_contents(base_path('openapi.json')), true);

    expect($committed)->not->toBeNull('openapi.json is missing or unreadable');

    expect(generatedDocument())->toEqual(
        $committed,
        'The OpenAPI document drifted from the code. Run `php artisan scramble:export` and commit the result.',
    );
});

it('documents every route of the API surface', function () {
    $documented = collect(generatedDocument()['paths'])
        ->flatMap(fn (array $methods, string $path) => collect($methods)
            ->keys()
            ->map(fn (string $method) => strtoupper($method).' '.ltrim($path, '/')))
        ->sort()
        ->values();

    $registered = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/'))
        ->flatMap(fn ($route) => collect($route->methods())
            ->reject(fn (string $method) => in_array($method, ['HEAD', 'OPTIONS'], true))
            ->map(fn (string $method) => $method.' '.str_replace('api/v1/', '', $route->uri())))
        ->sort()
        ->values();

    expect($documented->all())->toBe($registered->all());
});

it('describes errors with the envelope this API actually returns', function () {
    $responses = generatedDocument()['components']['responses'];

    foreach (['ValidationException', 'AuthenticationException', 'AuthorizationException'] as $name) {
        $schema = $responses[$name]['content']['application/json']['schema'];

        expect($schema['properties'])->toHaveKey('error')
            ->and($schema['properties']['error']['properties'])
            ->toHaveKeys(['code', 'message', 'details', 'trace_id']);
    }
});

it('documents the business refusals of check-out', function () {
    $codes = generatedDocument()['paths']['/loans']['post']['responses'][409]['content']['application/json']['schema']['properties']['error']['properties']['code']['enum'];

    expect($codes)->toBe([
        'LOAN_NO_COPIES',
        'LOAN_LIMIT_REACHED',
        'LOAN_MEMBER_OVERDUE',
        'LOAN_DUPLICATE_TITLE',
        'LOAN_USER_NOT_MEMBER',
        'LOAN_USER_INACTIVE',
    ]);
});

it('marks login and register public and everything else bearer authenticated', function () {
    $document = generatedDocument();

    expect($document['security'])->toBe([['http' => []]])
        ->and($document['paths']['/auth/login']['post']['security'])->toBe([])
        ->and($document['paths']['/auth/register']['post']['security'])->toBe([])
        ->and($document['paths']['/books']['get'])->not->toHaveKey('security');
});

it('restricts the documentation UI outside local to administrators', function () {
    // The suite runs with APP_ENV=testing, so the local bypass does not apply.
    $this->getJson('/docs/api.json')->assertForbidden();

    $this->actingAs(User::factory()->member()->create())->get('/docs/api')->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())->get('/docs/api')->assertOk();
});
