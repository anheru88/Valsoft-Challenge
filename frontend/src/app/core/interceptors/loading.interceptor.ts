import { inject } from '@angular/core';
import { HttpInterceptorFn } from '@angular/common/http';
import { finalize } from 'rxjs';
import { LoadingStore } from '../loading.store';

/**
 * Drives the global progress bar (PRD 8.4, the optional bar): one in-flight
 * request lights it, and it clears only when the last one settles.
 *
 * `finalize` fires on success, error and cancellation alike, so the count can
 * never be left stuck above zero by a request that failed or was switched
 * away from.
 */
export const loadingInterceptor: HttpInterceptorFn = (request, next) => {
  const loading = inject(LoadingStore);
  loading.begin();

  return next(request).pipe(finalize(() => loading.end()));
};
