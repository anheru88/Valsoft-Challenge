import { Injectable, computed, signal } from '@angular/core';
import { User, Role } from './models';

const TOKEN_KEY = 'librarium.token';

/** Estado global de sesión (signals). Fuente de verdad del rol en la UI;
 *  la autorización real siempre la aplica el API. */
@Injectable({ providedIn: 'root' })
export class AuthStore {
  private readonly _user = signal<User | null>(null);
  private readonly _token = signal<string | null>(localStorage.getItem(TOKEN_KEY));

  readonly user = this._user.asReadonly();
  readonly token = this._token.asReadonly();
  readonly isAuthenticated = computed(() => this._token() !== null);
  readonly role = computed<Role | null>(() => this._user()?.role ?? null);
  readonly isStaff = computed(() => this.role() === 'admin' || this.role() === 'librarian');
  readonly isAdmin = computed(() => this.role() === 'admin');

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
