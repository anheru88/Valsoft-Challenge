import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';
import { Permission } from '../models';

/**
 * Protege una ruta por capacidad en lugar de por rol.
 *
 * Uso: `canActivate: [authGuard, permissionGuard(['dashboard.view'])]`.
 * Basta con tener una de las capacidades indicadas.
 */
export function permissionGuard(allowed: Permission[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);

    return auth.canAny(allowed) ? true : router.createUrlTree(['/forbidden']);
  };
}
