import { inject } from '@angular/core';
import { catchError, map, of } from 'rxjs';
import { AuthStore } from '../../../core/auth.store';
import { AuthApiService } from './auth-api.service';

/**
 * Restores the session before the first route is resolved.
 *
 * The token survives a reload in `localStorage`, but the account behind it does
 * not: without this, a refresh leaves the guards with no capabilities and every
 * protected screen bounces to `/forbidden`. Asking `GET /auth/me` also settles
 * whether the token is still valid — a revoked one (role change, deactivation)
 * answers 401 and the session is dropped here rather than mid-navigation.
 */
export function restoreSession() {
  const auth = inject(AuthStore);
  const api = inject(AuthApiService);

  if (!auth.isAuthenticated()) {
    return of(true);
  }

  return api.me().pipe(
    map(({ data }) => {
      auth.setUser(data);

      return true;
    }),
    catchError(() => {
      auth.clearSession();

      return of(true);
    }),
  );
}
