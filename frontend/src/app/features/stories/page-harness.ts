import { Provider } from '@angular/core';
import { provideRouter } from '@angular/router';
import { applicationConfig } from '@storybook/angular';
import { AuthStore } from '../../core/auth.store';
import { Permission, Role, User } from '../../core/models';

/**
 * Shared scaffolding for the page stories.
 *
 * A page is not an isolated piece: it needs a router, and it reads the session
 * to decide what it offers. Seeding a concrete session here is what lets the
 * same screen be reviewed as an administrator, a librarian or a member.
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
 * Decorator: renders the page with a session of the given role.
 *
 * The wildcard route is required — without it the router tries to match
 * Storybook's own iframe URL and aborts the bootstrap with NG04002.
 */
export function asRole(role: Role) {
  return applicationConfig({
    providers: [provideRouter([{ path: '**', children: [] }]), authStoreFor(role)],
  });
}
