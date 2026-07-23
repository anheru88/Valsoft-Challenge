import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';

/**
 * Keeps an authenticated visitor out of the sign-in screens.
 *
 * Without it, `/` matches the auth layout first and a logged-in user is shown a
 * login form they have no use for.
 */
export const guestGuard: CanActivateFn = () => {
  const auth = inject(AuthStore);
  const router = inject(Router);

  if (!auth.isAuthenticated()) {
    return true;
  }

  return router.createUrlTree([auth.can('dashboard.view') ? '/dashboard' : '/books']);
};
