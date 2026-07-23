import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { PagedStore } from '../../../core/api/paged-store';
import { Category, Paginated } from '../../../core/models';
import { CategoriesApiService, CategoryQuery } from './categories-api.service';

const DEFAULT_QUERY: CategoryQuery = { sort: 'name', direction: 'asc', page: 1, per_page: 15 };

@Injectable()
export class CategoriesStore extends PagedStore<Category, CategoryQuery> {
  private readonly api = inject(CategoriesApiService);

  readonly categories = this.items;

  constructor() {
    super();
    this.start({ ...DEFAULT_QUERY });
  }

  protected fetch(query: CategoryQuery): Observable<Paginated<Category>> {
    return this.api.list(query);
  }
}
