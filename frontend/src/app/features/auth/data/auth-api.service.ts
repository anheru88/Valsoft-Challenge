import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { User } from '../../../core/models';

/** Envelope every single-resource endpoint returns (API specification 1). */
interface Envelope<T> {
  data: T;
}

export interface Credentials {
  email: string;
  password: string;
}

export interface Registration {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface Session {
  token: string;
  token_type: string;
  user: User;
}

export interface PasswordChange {
  current_password: string;
  password: string;
  password_confirmation: string;
}

/**
 * The authentication endpoints (API specification 2).
 *
 * Paths are relative: `apiBaseInterceptor` resolves them against the configured
 * server, so this service says nothing about where the API lives.
 */
@Injectable({ providedIn: 'root' })
export class AuthApiService {
  private readonly http = inject(HttpClient);

  login(credentials: Credentials): Observable<Envelope<Session>> {
    return this.http.post<Envelope<Session>>('auth/login', credentials);
  }

  register(registration: Registration): Observable<Envelope<Session>> {
    return this.http.post<Envelope<Session>>('auth/register', registration);
  }

  /** Revokes the current token; the others survive (FR-AUTH-4). */
  logout(): Observable<void> {
    return this.http.post<void>('auth/logout', {});
  }

  /** The authenticated account, with its effective capabilities (FR-AUTH-6). */
  me(): Observable<Envelope<User>> {
    return this.http.get<Envelope<User>>('auth/me');
  }

  /**
   * Changes the caller's own password (API specification 2). A wrong current
   * password comes back as `422 CURRENT_PASSWORD_INVALID`. On success the
   * server revokes every *other* token — this session's stays valid — so the
   * screen has nothing to clean up.
   */
  changePassword(change: PasswordChange): Observable<void> {
    return this.http.put<void>('auth/password', change);
  }
}
