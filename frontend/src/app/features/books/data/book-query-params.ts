import { ParamMap, Params } from '@angular/router';
import { BookQuery } from './books-api.service';

/**
 * The catalogue query, back and forth with the URL.
 *
 * A filtered catalogue has to survive being copied into a chat window or a
 * bookmark (PRD 4.2), which means the URL — not a signal — is where the query
 * really lives. It is also what makes the category chips on a book's detail
 * page work: they are ordinary links into `/books?category_id=…`.
 */
export function bookQueryFromParams(params: ParamMap, fallback: BookQuery): BookQuery {
  const number = (key: string): number | null => {
    const raw = params.get(key);
    const value = Number(raw);

    return raw !== null && Number.isFinite(value) ? value : null;
  };

  return {
    ...fallback,
    q: params.get('q') ?? undefined,
    category_id: number('category_id'),
    author_id: number('author_id'),
    available: params.get('available') === 'true' ? true : undefined,
    year_from: number('year_from'),
    year_to: number('year_to'),
    sort: params.get('sort') ?? fallback.sort,
    direction: params.get('direction') === 'desc' ? 'desc' : fallback.direction,
    page: number('page') ?? 1,
    per_page: number('per_page') ?? fallback.per_page,
  };
}

/**
 * The reverse. Defaults are left out rather than spelled out: a URL that says
 * `?sort=title&direction=asc&page=1` for the untouched catalogue is noise, and
 * noise is what stops people from sharing the link.
 */
export function bookQueryToParams(query: BookQuery, defaults: BookQuery): Params {
  const params: Params = {};
  const put = (key: string, value: unknown, fallback?: unknown): void => {
    if (value === null || value === undefined || value === '' || value === fallback) return;
    params[key] = value;
  };

  put('q', query.q);
  put('category_id', query.category_id);
  put('author_id', query.author_id);
  put('available', query.available === true ? true : undefined);
  put('year_from', query.year_from);
  put('year_to', query.year_to);
  put('sort', query.sort, defaults.sort);
  put('direction', query.direction, defaults.direction);
  put('page', query.page, 1);
  put('per_page', query.per_page, defaults.per_page);

  return params;
}
