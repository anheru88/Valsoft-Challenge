import { Component, computed, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { Role, User } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { SearchInput } from '../../../shared/ui/search-input';
import { RoleBadge } from '../../../shared/ui/role-badge';
import { ConfirmDialog } from '../../../shared/ui/confirm-dialog';

@Component({
  selector: 'lib-users-list-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatFormFieldModule,
            MatSelectModule, PageHeader, SearchInput, RoleBadge],
  templateUrl: './users-list-page.html',
  styles: [`
    .strong { font-weight: 600; } .compact { width: 170px; } .actions-col { width: 48px; text-align: right; }
    tr.inactive td { opacity: .55; }
    .state { font-size: 12px; font-weight: 600; }
    .state.ok { color: var(--lib-green-strong); } .state.off { color: var(--lib-ink-faint); }
  `],
})
export class UsersListPage {
  private readonly dialog = inject(MatDialog);
  private readonly snack = inject(MatSnackBar);

  readonly roleFilter = signal<Role | null>(null);
  readonly q = signal('');

  // Demostración — GET /api/v1/users?role&q&page
  readonly users = signal<User[]>([
    { id: 1,  name: 'Alicia Ferrer', email: 'alicia@biblioteca.org', role: 'admin', is_active: true, active_loans_count: 0, created_at: '2024-01-05' },
    { id: 9,  name: 'Luis Prado', email: 'luis@biblioteca.org', role: 'librarian', is_active: true, active_loans_count: 1, created_at: '2024-06-12' },
    { id: 34, name: 'Marta Ruiz', email: 'marta@example.com', role: 'member', is_active: true, active_loans_count: 2, created_at: '2025-01-10' },
    { id: 55, name: 'Iván Costa', email: 'ivan@example.com', role: 'member', is_active: false, active_loans_count: 0, created_at: '2025-03-22' },
  ]);

  readonly filteredUsers = computed(() => {
    const role = this.roleFilter(); const q = this.q().toLowerCase();
    return this.users().filter(u =>
      (!role || u.role === role) &&
      (!q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)));
  });

  onSearch(q: string): void { this.q.set(q); }

  toggleActive(u: User): void {
    // TODO API: PATCH /users/{id} { is_active }
    // 409 LAST_ADMIN_PROTECTED → 'No puedes desactivar al único administrador.'
    this.snack.open(u.is_active ? 'Cuenta desactivada' : 'Cuenta reactivada', undefined, { duration: 4000 });
  }

  confirmDelete(u: User): void {
    this.dialog.open(ConfirmDialog, { data: {
      title: '¿Eliminar la cuenta de ' + u.name + '?',
      message: (u.active_loans_count ?? 0) > 0
        ? 'Tiene ' + u.active_loans_count + ' préstamos activos: hay que registrarlos como devueltos antes.'
        : 'La cuenta se desactivará y dejará de poder iniciar sesión. El historial de préstamos se conserva.',
      confirmLabel: 'Eliminar cuenta', destructive: true,
    } }).afterClosed().subscribe(ok => {
      if (!ok) return;
      // TODO API: DELETE /users/{id}
      // 409 USER_HAS_ACTIVE_LOANS | LAST_ADMIN_PROTECTED → alerta con el motivo del servidor.
    });
  }
}
