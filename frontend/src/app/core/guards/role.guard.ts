import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';
import { Role } from '../models';

/**
 * Guards a route by role name.
 *
 * Prefer `permissionGuard`: asking for a capability lets a role created on the
 * server work without deploying the client. This stays for the few screens that
 * belong to one named role by definition.
 */
export function roleGuard(allowed: Role[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);
    const roles = auth.roles();

    return roles.some(role => allowed.includes(role)) ? true : router.createUrlTree(['/forbidden']);
  };
}
