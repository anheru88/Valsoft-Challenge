import { Provider } from '@angular/core';
import { provideRouter } from '@angular/router';
import { applicationConfig } from '@storybook/angular';
import { AuthStore } from '../../core/auth.store';
import { Permission, Role, User } from '../../core/models';

/**
 * Andamiaje común de las historias de página.
 *
 * Una página no es una pieza aislada: necesita router, y lee de la sesión para
 * decidir qué ofrece. Sembrar aquí una sesión concreta es lo que permite
 * revisar la misma pantalla como administrador, bibliotecario o socio.
 */
const PERMISSIONS: Record<Role, Permission[]> = {
  member: ['catalog.view'],
  librarian: [
    'catalog.view', 'catalog.manage', 'loans.manage', 'loans.view-any',
    'users.view', 'users.create-member', 'dashboard.view',
  ],
  admin: [
    'catalog.view', 'catalog.manage', 'loans.manage', 'loans.view-any',
    'users.view', 'users.view-any', 'users.create-any', 'users.create-member',
    'users.manage', 'dashboard.view', 'reports.view',
  ],
};

const NAMES: Record<Role, string> = {
  admin: 'Alicia Ferrer',
  librarian: 'Luis Prado',
  member: 'Marta Ruiz',
};

export function sessionUser(role: Role): User {
  return {
    id: role === 'admin' ? 1 : role === 'librarian' ? 9 : 34,
    name: NAMES[role],
    email: `${role}@librarium.test`,
    roles: [role],
    permissions: PERMISSIONS[role],
    is_active: true,
    active_loans_count: role === 'member' ? 2 : 0,
    created_at: '2026-01-05',
  };
}

function authStoreFor(role: Role): Provider {
  return {
    provide: AuthStore,
    useFactory: () => {
      const store = new AuthStore();
      store.startSession('storybook-token', sessionUser(role));

      return store;
    },
  };
}

/**
 * Decorador: la página se renderiza con una sesión del rol indicado.
 *
 * La ruta comodín es necesaria: sin ella el router intenta emparejar la URL del
 * propio iframe de Storybook y aborta el arranque con NG04002.
 */
export function asRole(role: Role) {
  return applicationConfig({
    providers: [provideRouter([{ path: '**', children: [] }]), authStoreFor(role)],
  });
}
