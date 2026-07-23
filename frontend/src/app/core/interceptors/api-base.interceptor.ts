import { HttpInterceptorFn } from '@angular/common/http';
import { environment } from '../../../environments/environment';

/**
 * Resolves relative paths against the API base.
 *
 * Services ask for `books` or `loans/12/return`; where the server lives and
 * which version is in use is configuration, not something repeated across
 * twenty files. An absolute URL is left alone — it may well point at a third
 * party.
 */
export const apiBaseInterceptor: HttpInterceptorFn = (req, next) => {
  if (/^https?:\/\//i.test(req.url)) {
    return next(req);
  }

  const base = environment.apiUrl.replace(/\/$/, '');
  const path = req.url.replace(/^\//, '');

  return next(req.clone({ url: `${base}/${path}` }));
};
