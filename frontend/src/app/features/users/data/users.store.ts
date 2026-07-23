import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { PagedStore } from '../../../core/api/paged-store';
import { Paginated, User } from '../../../core/models';
import { UserQuery, UsersApiService } from './users-api.service';

const DEFAULT_QUERY: UserQuery = { sort: 'name', direction: 'asc', page: 1, per_page: 15 };

/**
 * The account list.
 *
 * An administrator sees every account; a librarian's list comes back scoped to
 * members by the server (PRD 8.3 amendment), so nothing has to be filtered out
 * here — and nothing could be, since hiding rows on the client hides them from
 * the reader and nobody else.
 */
@Injectable()
export class UsersStore extends PagedStore<User, UserQuery> {
  private readonly api = inject(UsersApiService);

  readonly users = this.items;

  constructor() {
    super();
    this.start({ ...DEFAULT_QUERY });
  }

  protected fetch(query: UserQuery): Observable<Paginated<User>> {
    return this.api.list(query);
  }
}
