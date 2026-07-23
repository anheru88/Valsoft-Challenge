import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { catchError, throwError } from 'rxjs';
import { AuthStore } from '../auth.store';

/** 401 → sesión expirada; 403 → /forbidden; 5xx → toast con trace_id.
 *  Los 409/422 NO se tratan aquí: cada vista los muestra en línea. */
export const apiErrorInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthStore);
  const router = inject(Router);
  const snack = inject(MatSnackBar);

  return next(req).pipe(
    catchError((err: HttpErrorResponse) => {
      if (err.status === 401) {
        auth.clearSession();
        router.navigate(['/login'], { queryParams: { reason: 'expired' } });
      } else if (err.status === 403) {
        router.navigate(['/forbidden']);
      } else if (err.status >= 500) {
        const traceId = err.error?.error?.trace_id ?? '';
        snack.open('Algo salió mal. Vuelve a intentarlo.' + (traceId ? ' Ref: ' + traceId : ''), 'Cerrar', { duration: 6000 });
      }
      return throwError(() => err);
    }),
  );
};
