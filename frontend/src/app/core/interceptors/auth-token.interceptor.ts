import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { environment } from '../../../environments/environment';
import { AuthStore } from '../auth.store';

/**
 * Attaches the session token (FR-AUTH-1).
 *
 * Only to requests aimed at our own API: sending the credential to any host the
 * client happens to call would be giving it away.
 */
export const authTokenInterceptor: HttpInterceptorFn = (req, next) => {
  const token = inject(AuthStore).token();

  if (!token || !isOwnApi(req.url)) {
    return next(req);
  }

  return next(req.clone({ setHeaders: { Authorization: 'Bearer ' + token } }));
};

function isOwnApi(url: string): boolean {
  const base = environment.apiUrl;

  return /^https?:\/\//i.test(base) ? url.startsWith(base) : url.startsWith(base) || url.startsWith('/api');
}
