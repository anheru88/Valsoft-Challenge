import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { PagedStore } from '../../../core/api/paged-store';
import { Author, Paginated } from '../../../core/models';
import { AuthorQuery, AuthorsApiService } from './authors-api.service';

const DEFAULT_QUERY: AuthorQuery = { sort: 'name', direction: 'asc', page: 1, per_page: 15 };

@Injectable()
export class AuthorsStore extends PagedStore<Author, AuthorQuery> {
  private readonly api = inject(AuthorsApiService);

  readonly authors = this.items;

  constructor() {
    super();
    this.start({ ...DEFAULT_QUERY });
  }

  protected fetch(query: AuthorQuery): Observable<Paginated<Author>> {
    return this.api.list(query);
  }
}
