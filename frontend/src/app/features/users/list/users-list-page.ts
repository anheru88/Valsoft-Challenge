import { Component, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { businessMessage } from '../../../core/api/error-message';
import { Role, User } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { RoleBadge } from '../../../shared/ui/role-badge';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { Skeleton } from '../../../shared/ui/skeleton';
import { EmptyState } from '../../../shared/ui/empty-state';
import { ErrorState } from '../../../shared/ui/error-state';
import { ConfirmDialog } from '../../../shared/ui/confirm-dialog';
import { UsersApiService } from '../data/users-api.service';
import { UsersStore } from '../data/users.store';

@Component({
  selector: 'lib-users-list-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatFormFieldModule,
            MatSelectModule, MatPaginatorModule,
            PageHeader, SearchInput, RoleBadge, InlineAlert, Skeleton, EmptyState, ErrorState],
  providers: [UsersStore],
  templateUrl: './users-list-page.html',
  styles: [`
    .strong { font-weight: 600; } .compact { width: 170px; } .actions-col { width: 48px; text-align: right; }
    tr.inactive td { opacity: .55; }
    .state { font-size: 12px; font-weight: 600; }
    .state.ok { color: var(--lib-green-strong); } .state.off { color: var(--lib-ink-faint); }
    mat-paginator { border-top: 1px solid var(--lib-line); }
  `],
})
export class UsersListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);
  private readonly store = inject(UsersStore);
  private readonly api = inject(UsersApiService);

  readonly loading = this.store.loading;
  readonly error = this.store.error;
  readonly users = this.store.users;
  readonly meta = this.store.meta;
  readonly actionError = signal<string | null>(null);

  readonly roleFilter = computed<Role | null>(() => this.store.query().role ?? null);
  readonly q = computed(() => this.store.query().q ?? '');

  /** Filtering happens on the server, so a page shows what the filter asked for. */
  onSearch(q: string): void { this.store.patchQuery({ q }); }
  setRole(role: Role | null): void { this.store.patchQuery({ role }); }
  onPage(e: PageEvent): void { this.store.setPage(e.pageIndex + 1, e.pageSize); }
  load(): void { this.store.reload(); }

  toggleActive(u: User): void {
    this.actionError.set(null);

    this.api.changeStatus(u.id, !u.is_active).subscribe({
      next: () => {
        this.snack.open(u.is_active ? 'Account deactivated' : 'Account reactivated', undefined, { duration: 4000 });
        this.store.reload();
      },
      // 409 LAST_ADMIN_PROTECTED: the library must keep one administrator.
      error: (err: unknown) => this.actionError.set(businessMessage(err)),
    });
  }

  confirmDelete(u: User): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: 'Delete the account of ' + u.name + '?',
      message: (u.active_loans_count ?? 0) > 0
        ? 'They hold ' + u.active_loans_count + ' active loans, which have to be checked in first.'
        : 'The account will be deactivated and can no longer sign in. Its loan history is kept.',
      confirmLabel: 'Delete account', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;

      this.actionError.set(null);
      this.api.remove(u.id).subscribe({
        next: () => {
          this.snack.open('Account deleted', undefined, { duration: 4000 });
          this.store.reload();
        },
        // 409 USER_HAS_ACTIVE_LOANS or LAST_ADMIN_PROTECTED, in the server's own
        // terms: the reason a deletion was refused is the useful part.
        error: (err: unknown) => this.actionError.set(businessMessage(err)),
      });
    });
  }
}
