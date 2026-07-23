<?php

declare(strict_types=1);

use App\Library\Shared\Application\SortParams;
use Illuminate\Http\Request;

const ALLOWED = ['title', 'created_at'];

it('accepts a whitelisted field', function () {
    $sort = SortParams::make('title', 'desc', ALLOWED, 'created_at');

    expect($sort->field)->toBe('title')
        ->and($sort->direction)->toBe('desc')
        ->and($sort->isDescending())->toBeTrue();
});

it('never lets an unlisted field reach the query', function () {
    $sort = SortParams::make('password', 'asc', ALLOWED, 'created_at');

    expect($sort->field)->toBe('created_at');
});

it('normalises the direction and defaults to ascending', function () {
    expect(SortParams::make('title', 'DESC', ALLOWED, 'title')->direction)->toBe('desc')
        ->and(SortParams::make('title', 'sideways', ALLOWED, 'title')->direction)->toBe('asc')
        ->and(SortParams::make('title', null, ALLOWED, 'title')->direction)->toBe('asc');
});

it('reads sort and direction from the request', function () {
    $sort = SortParams::fromRequest(
        Request::create('/books?sort=title&direction=desc'),
        ALLOWED,
        'created_at',
    );

    expect($sort->field)->toBe('title')
        ->and($sort->direction)->toBe('desc');
});
