import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Envelope, toParams } from '../../../core/api/envelope';
import { Paginated, Role, User } from '../../../core/models';

export interface UserQuery {
  q?: string;
  role?: Role | null;
  is_active?: boolean;
  sort?: 'name' | 'email' | 'created_at';
  direction?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

/**
 * A user is written with a single `role` and read back with `roles[]` plus the
 * effective `permissions[]` (ADR-11).
 */
export interface UserPayload {
  name: string;
  email: string;
  role?: Role;
  password?: string;
}

/** The account endpoints (API specification 7). */
@Injectable({ providedIn: 'root' })
export class UsersApiService {
  private readonly http = inject(HttpClient);

  list(query: UserQuery = {}): Observable<Paginated<User>> {
    return this.http.get<Paginated<User>>('users', { params: toParams({ ...query }) });
  }

  get(id: number): Observable<Envelope<User>> {
    return this.http.get<Envelope<User>>(`users/${id}`);
  }

  create(payload: UserPayload): Observable<Envelope<User>> {
    return this.http.post<Envelope<User>>('users', payload);
  }

  update(id: number, payload: UserPayload): Observable<Envelope<User>> {
    return this.http.put<Envelope<User>>(`users/${id}`, payload);
  }

  /** Deactivating revokes every token the account holds. */
  changeStatus(id: number, isActive: boolean): Observable<Envelope<User>> {
    return this.http.patch<Envelope<User>>(`users/${id}/status`, { is_active: isActive });
  }

  /** Soft delete, so the loan history keeps a row to point at. */
  remove(id: number): Observable<void> {
    return this.http.delete<void>(`users/${id}`);
  }
}
