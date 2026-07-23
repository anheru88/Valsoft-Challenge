import { Component, input } from '@angular/core';

/** Cabecera estándar de página: título + subtítulo + acciones (ng-content). */
@Component({
  selector: 'lib-page-header',
  standalone: true,
  template: `
    <header class="page-header">
      <div>
        <h1>{{ title() }}</h1>
        @if (subtitle()) { <p class="muted">{{ subtitle() }}</p> }
      </div>
      <div class="actions"><ng-content /></div>
    </header>
  `,
  styles: [`
    .page-header {
      display: flex; align-items: flex-end; justify-content: space-between;
      gap: var(--sp-4); flex-wrap: wrap; margin-bottom: var(--sp-5);
    }
    h1 { margin: 0; font-size: 26px; }
    p { margin: var(--sp-1) 0 0; }
    .actions { display: flex; gap: var(--sp-3); }
  `],
})
export class PageHeader {
  readonly title = input.required<string>();
  readonly subtitle = input<string>();
}
