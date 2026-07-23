import { HttpParams } from '@angular/common/http';

/** Envelope every single-resource endpoint returns (API specification 1). */
export interface Envelope<T> {
  data: T;
}

/**
 * Query string builder for the list endpoints.
 *
 * Absent means absent: a filter set to `null`, `undefined` or the empty string
 * is dropped rather than sent blank, because the API validates what it receives
 * and `?category_id=` is not a valid integer.
 */
export function toParams(source: Record<string, string | number | boolean | null | undefined>): HttpParams {
  let params = new HttpParams();

  for (const [key, value] of Object.entries(source)) {
    if (value === null || value === undefined || value === '') {
      continue;
    }
    params = params.set(key, String(value));
  }

  return params;
}
