import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { catchError, throwError } from 'rxjs';
import { AuthStore } from '../auth.store';

/**
 * Global handling for the failures no single page can answer: an expired
 * session, a forbidden page, a server that broke.
 *
 * 409 and 422 are deliberately absent — those are answers about the request,
 * and the view that made it shows them inline (PRD 5).
 *
 * Nothing is hijacked while there is no session: a guest being refused is being
 * told about their credentials, not about a page they cannot reach, and
 * navigating away would swallow the message the sign-in form is about to show.
 */
export const apiErrorInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthStore);
  const router = inject(Router);
  const snack = inject(MatSnackBar);

  return next(req).pipe(
    catchError((err: HttpErrorResponse) => {
      const hasSession = auth.isAuthenticated();

      if (err.status === 401 && hasSession) {
        auth.clearSession();
        router.navigate(['/login'], { queryParams: { reason: 'expired' } });
      } else if (err.status === 403 && hasSession) {
        router.navigate(['/forbidden']);
      } else if (err.status >= 500) {
        const traceId = err.error?.error?.trace_id ?? '';
        snack.open('Algo salió mal. Vuelve a intentarlo.' + (traceId ? ' Ref: ' + traceId : ''), 'Cerrar', { duration: 6000 });
      }
      return throwError(() => err);
    }),
  );
};
