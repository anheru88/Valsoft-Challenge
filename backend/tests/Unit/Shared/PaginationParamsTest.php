<?php

declare(strict_types=1);

use App\Library\Shared\Application\PaginationParams;
use Illuminate\Http\Request;

it('falls back to the configured defaults', function () {
    $params = PaginationParams::make(null, null);

    expect($params->page)->toBe(1)
        ->and($params->perPage)->toBe(config('library.pagination.default_per_page'));
});

it('caps per_page so a client cannot ask for an unbounded page', function () {
    $params = PaginationParams::make(1, 5_000);

    expect($params->perPage)->toBe(config('library.pagination.max_per_page'));
});

it('clamps nonsensical values to the first page and a single row', function () {
    $params = PaginationParams::make(-3, 0);

    expect($params->page)->toBe(1)
        ->and($params->perPage)->toBe(1);
});

it('reads page and per_page from the request', function () {
    $params = PaginationParams::fromRequest(Request::create('/books?page=3&per_page=25'));

    expect($params->page)->toBe(3)
        ->and($params->perPage)->toBe(25);
});
