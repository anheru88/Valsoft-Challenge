import { Injectable, computed, signal } from '@angular/core';
import { Permission, Role, User } from './models';

const TOKEN_KEY = 'librarium.token';

/**
 * Estado global de sesión (signals).
 *
 * La UI decide qué mostrar a partir de las **capacidades** que el API reporta,
 * no del nombre del rol: así un rol nuevo definido en el servidor funciona sin
 * tocar el cliente. La autorización de verdad siempre la aplica el API; esto
 * solo evita ofrecer acciones que se sabe que van a fallar.
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

  /** El rol principal, para etiquetas y saludos. Nunca para decidir permisos. */
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
