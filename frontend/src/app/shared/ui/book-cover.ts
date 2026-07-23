import { Component, input } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';

/**
 * A book's cover, or a stand-in for it.
 *
 * Most of the catalogue has no `cover_url`, so the fallback is the normal case
 * rather than the exception, and it has to hold the same shape — a list whose
 * rows change height depending on whether an image exists is a list that jumps
 * while it loads.
 */
@Component({
  selector: 'lib-book-cover',
  standalone: true,
  imports: [MatIconModule],
  template: `
    @if (url()) {
      <img [src]="url()" [alt]="'Cover of ' + title()" loading="lazy" />
    } @else {
      <span class="fallback" aria-hidden="true"><mat-icon>menu_book</mat-icon></span>
    }
  `,
  styles: [`
    :host {
      display: block; overflow: hidden;
      aspect-ratio: 2 / 3; border-radius: var(--lib-radius-sm);
      background: var(--lib-surface-2);
    }
    img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fallback { display: grid; place-items: center; width: 100%; height: 100%; color: var(--lib-ink-faint); }
    mat-icon { font-size: 60%; width: auto; height: auto; }
  `],
})
export class BookCover {
  readonly url = input<string | null | undefined>(null);
  readonly title = input('');
}
