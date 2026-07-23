import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';
import { Role } from '../models';

/** Uso: canActivate: [authGuard, roleGuard(['admin','librarian'])] */
export function roleGuard(allowed: Role[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);
    const role = auth.role();
    return role && allowed.includes(role) ? true : router.createUrlTree(['/forbidden']);
  };
}
