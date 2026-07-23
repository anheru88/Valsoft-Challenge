import { Injectable, computed, signal } from '@angular/core';
import { Permission, Role, User } from './models';

const TOKEN_KEY = 'librarium.token';

/**
 * Global session state (signals).
 *
 * The interface decides what to show from the **capabilities** the API reports,
 * not from the role name: a role defined on the server then works without
 * touching the client. Real authorization is always applied by the API; this
 * only avoids offering actions that are known to fail.
 */
@Injectable({ providedIn: 'root' })
export class AuthStore {
  private readonly _user = signal<User | null>(null);
  private readonly _token = signal<string | null>(localStorage.getItem(TOKEN_KEY));

  readonly user = this._user.asReadonly();
  readonly token = this._token.asReadonly();
  readonly isAuthenticated = computed(() => this._token() !== null);

  readonly roles = computed<Role[]>(() => this._user()?.roles ?? []);
  readonly permissions = computed<Permission[]>(() => this._user()?.permissions ?? []);

  /** The primary role, for labels and greetings. Never for deciding access. */
  readonly role = computed<Role | null>(() => this.roles()[0] ?? null);

  readonly isStaff = computed(() => this.canAny(['catalog.manage', 'loans.manage', 'dashboard.view']));
  readonly isAdmin = computed(() => this.can('users.manage'));

  can(permission: Permission): boolean {
    return this.permissions().includes(permission);
  }

  canAny(permissions: Permission[]): boolean {
    return permissions.some(permission => this.can(permission));
  }

  startSession(token: string, user: User): void {
    localStorage.setItem(TOKEN_KEY, token);
    this._token.set(token);
    this._user.set(user);
  }

  setUser(user: User): void { this._user.set(user); }

  clearSession(): void {
    localStorage.removeItem(TOKEN_KEY);
    this._token.set(null);
    this._user.set(null);
  }
}
