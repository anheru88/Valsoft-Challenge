<?php

declare(strict_types=1);

namespace App\Library\Shared\Infrastructure;

use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared plumbing for the Eloquent repository implementations: the sort and
 * pagination translation every list query repeats. Concrete repositories keep
 * their domain-meaningful methods and never hand a builder back to callers
 * (RFC 6).
 *
 * @template TModel of Model
 */
abstract class EloquentRepository
{
    /**
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function applySort(Builder $query, SortParams $sort): Builder
    {
        return $query->orderBy($sort->field, $sort->direction);
    }

    /**
     * @param  Builder<TModel>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    protected function paginate(Builder $query, PaginationParams $pagination): LengthAwarePaginator
    {
        return $query->paginate(
            perPage: $pagination->perPage,
            page: $pagination->page,
        );
    }
}
