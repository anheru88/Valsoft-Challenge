import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthStore } from '../auth.store';
import { Role } from '../models';

/**
 * Protege una ruta por nombre de rol.
 *
 * Prefiere `permissionGuard`: preguntar por capacidad hace que un rol nuevo
 * creado en el servidor funcione sin desplegar el cliente. Esto queda para los
 * pocos casos en que la pantalla es de un rol concreto por definición.
 */
export function roleGuard(allowed: Role[]): CanActivateFn {
  return () => {
    const auth = inject(AuthStore);
    const router = inject(Router);
    const roles = auth.roles();

    return roles.some(role => allowed.includes(role)) ? true : router.createUrlTree(['/forbidden']);
  };
}
