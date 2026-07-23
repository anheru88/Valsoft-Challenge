import { Component, computed, input } from '@angular/core';
import { Role } from '../../core/models';

const LABELS: Record<Role, string> = { admin: 'Admin', librarian: 'Bibliotecario', member: 'Socio' };

@Component({
  selector: 'lib-role-badge',
  standalone: true,
  template: `<span class="badge" [class]="role()">{{ label() }}</span>`,
  styles: [`
    .badge {
      display: inline-block; padding: 2px 10px; border-radius: 999px;
      font-size: 12px; font-weight: 600; letter-spacing: .02em;
      border: 1px solid transparent;
    }
    .admin     { background: var(--lib-brass-soft); color: var(--lib-brass);  border-color: var(--lib-brass); }
    .librarian { background: var(--lib-info-soft);  color: var(--lib-info);   border-color: var(--lib-info); }
    .member    { background: var(--lib-surface-2);  color: var(--lib-ink-soft); border-color: var(--lib-line); }
  `],
})
export class RoleBadge {
  readonly role = input.required<Role>();
  readonly label = computed(() => LABELS[this.role()]);
}
