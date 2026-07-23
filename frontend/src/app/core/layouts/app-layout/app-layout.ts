import { Component, ElementRef, HostListener, ViewChild, computed, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { BreakpointObserver, Breakpoints } from '@angular/cdk/layout';
import { toSignal } from '@angular/core/rxjs-interop';
import { map } from 'rxjs';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatMenuModule } from '@angular/material/menu';
import { AuthStore } from '../../auth.store';
import { RoleBadge } from '../../../shared/ui/role-badge';

@Component({
  selector: 'lib-app-layout',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, FormsModule,
            MatSidenavModule, MatListModule, MatIconModule, MatButtonModule, MatMenuModule, RoleBadge],
  templateUrl: './app-layout.html',
  styleUrl: './app-layout.scss',
})
export class AppLayout {
  readonly auth = inject(AuthStore);
  private readonly router = inject(Router);
  private readonly bp = inject(BreakpointObserver);

  @ViewChild('searchBox') searchBox?: ElementRef<HTMLInputElement>;

  searchTerm = '';
  readonly dark = signal(document.body.classList.contains('dark-theme'));
  readonly isHandset = toSignal(
    this.bp.observe([Breakpoints.Handset]).pipe(map(r => r.matches)),
    { initialValue: false },
  );

  /** Atajo de teclado "/" → foco en la búsqueda global */
  @HostListener('document:keydown./', ['$event'])
  focusSearch(ev: Event): void {
    const target = ev.target as HTMLElement;
    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;
    ev.preventDefault();
    this.searchBox?.nativeElement.focus();
  }

  goSearch(ev: Event): void {
    ev.preventDefault();
    const q = this.searchTerm.trim();
    if (q.length >= 2) this.router.navigate(['/search'], { queryParams: { q } });
  }

  toggleTheme(): void {
    document.body.classList.toggle('dark-theme');
    this.dark.set(document.body.classList.contains('dark-theme'));
    localStorage.setItem('librarium.theme', this.dark() ? 'dark' : 'light');
  }

  logout(): void {
    // TODO: AuthApiService.logout() → POST /auth/logout
    this.auth.clearSession();
    this.router.navigate(['/login']);
  }
}
