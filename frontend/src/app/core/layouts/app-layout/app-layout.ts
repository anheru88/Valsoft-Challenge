import { Component, ElementRef, HostListener, ViewChild, inject } from '@angular/core';
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
import { ThemeStore } from '../../theme.store';
import { AuthApiService } from '../../../features/auth/data/auth-api.service';
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
  private readonly theme = inject(ThemeStore);
  private readonly api = inject(AuthApiService);
  private readonly router = inject(Router);
  private readonly bp = inject(BreakpointObserver);

  @ViewChild('searchBox') searchBox?: ElementRef<HTMLInputElement>;

  searchTerm = '';
  readonly dark = this.theme.isDark;
  readonly isHandset = toSignal(
    this.bp.observe([Breakpoints.Handset]).pipe(map(r => r.matches)),
    { initialValue: false },
  );

  /** Keyboard shortcut "/" focuses the global search. */
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

  toggleTheme(): void { this.theme.toggle(); }

  /**
   * The token is revoked server-side, then the session is dropped here.
   *
   * The local half happens either way: a network failure must not leave someone
   * signed in on a shared desk machine, and a token the server never heard
   * about being revoked is the lesser problem.
   */
  logout(): void {
    this.api.logout().subscribe({
      next: () => this.endSession(),
      error: () => this.endSession(),
    });
  }

  private endSession(): void {
    this.auth.clearSession();
    this.router.navigate(['/login']);
  }
}
