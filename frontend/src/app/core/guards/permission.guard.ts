import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';
import { Permission } from '../models';

/**
 * Guards a route by capability rather than by role.
 *
 * Usage: `canActivate: [authGuard, permissionGuard(['dashboard.view'])]`.
 * Holding any one of the listed capabilities is enough.
 */
export function permissionGuard(allowed: Permission[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);

    return auth.canAny(allowed) ? true : router.createUrlTree(['/forbidden']);
  };
}
