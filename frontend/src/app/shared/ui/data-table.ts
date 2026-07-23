import { Directive, Component, TemplateRef, computed, contentChildren, inject, input, output } from '@angular/core';
import { NgTemplateOutlet } from '@angular/common';
import { BreakpointObserver, Breakpoints } from '@angular/cdk/layout';
import { toSignal } from '@angular/core/rxjs-interop';
import { map } from 'rxjs';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { PageMeta } from '../../core/models';

/**
 * The cell renderer for one column.
 *
 * Usage: `<ng-template libCell="title" let-row>…</ng-template>`. The table owns
 * the structure — header, sorting, paging, the handset layout — and the page
 * owns what a cell says, which is the only part that differs between screens.
 */
@Directive({ selector: '[libCell]', standalone: true })
export class CellTemplate {
  readonly libCell = input.required<string>();
  readonly template = inject(TemplateRef<{ $implicit: unknown }>);
}

export interface ColumnDef {
  key: string;
  header: string;
  /** Sortable columns emit `sorted`; the server does the sorting. */
  sortable?: boolean;
  align?: 'start' | 'end';
  /** The column that names the row; the card layout uses it as its title. */
  primary?: boolean;
  /** Actions and the like: no header text, no label in the card layout. */
  bare?: boolean;
  /** Dropped from the card layout when the screen is a phone. */
  secondary?: boolean;
}

export interface SortState { field: string; direction: 'asc' | 'desc'; }

/**
 * A list of records, paged by the server.
 *
 * Every list screen in the product had grown its own copy of this table, its
 * own paginator wiring and its own idea of what to do on a narrow screen. That
 * last part is why it is worth sharing: PRD 2.3 asks tables to become card
 * lists on a phone rather than scroll sideways, and that is a decision to make
 * once, not six times.
 */
@Component({
  selector: 'lib-data-table',
  standalone: true,
  imports: [NgTemplateOutlet, MatPaginatorModule],
  template: `
    @if (isHandset()) {
      <ul class="cards" [attr.aria-label]="label()">
        @for (row of rows(); track trackBy()(row)) {
          <li class="card-row">
            @if (primaryColumn(); as col) {
              <div class="card-primary">
                <ng-container [ngTemplateOutlet]="templateFor(col.key)" [ngTemplateOutletContext]="{ $implicit: row }" />
              </div>
            }
            <dl class="card-meta">
              @for (col of cardColumns(); track col.key) {
                <div>
                  <dt>{{ col.header }}</dt>
                  <dd><ng-container [ngTemplateOutlet]="templateFor(col.key)" [ngTemplateOutletContext]="{ $implicit: row }" /></dd>
                </div>
              }
            </dl>
            @if (bareColumn(); as col) {
              <div class="card-actions">
                <ng-container [ngTemplateOutlet]="templateFor(col.key)" [ngTemplateOutletContext]="{ $implicit: row }" />
              </div>
            }
          </li>
        }
      </ul>
    } @else {
      <table class="lib-table" [attr.aria-label]="label()">
        <thead>
          <tr>
            @for (col of columns(); track col.key) {
              <th [class.num]="col.align === 'end'" [class.actions-col]="col.bare"
                  [attr.aria-sort]="ariaSort(col)" scope="col">
                @if (col.sortable) {
                  <button class="sort-btn" (click)="sorted.emit(col.key)">{{ col.header }} {{ indicator(col.key) }}</button>
                } @else if (col.bare) {
                  <span class="cdk-visually-hidden">{{ col.header }}</span>
                } @else {
                  {{ col.header }}
                }
              </th>
            }
          </tr>
        </thead>
        <tbody>
          @for (row of rows(); track trackBy()(row)) {
            <tr [class]="rowClass()(row)">
              @for (col of columns(); track col.key) {
                <td [class.num]="col.align === 'end'" [class.actions-col]="col.bare">
                  <ng-container [ngTemplateOutlet]="templateFor(col.key)" [ngTemplateOutletContext]="{ $implicit: row }" />
                </td>
              }
            </tr>
          }
        </tbody>
      </table>
    }

    @if (meta(); as m) {
      <mat-paginator [length]="m.total" [pageIndex]="m.current_page - 1" [pageSize]="m.per_page"
                     [pageSizeOptions]="pageSizes()" (page)="paged.emit($event)"
                     [attr.aria-label]="label() + ' pagination'" />
    }
  `,
  styles: [`
    :host { display: block; }
    mat-paginator { border-top: 1px solid var(--lib-line); }
    .sort-btn {
      all: unset; cursor: pointer; font: inherit; color: inherit;
      text-transform: inherit; letter-spacing: inherit;
    }
    .sort-btn:hover { color: var(--lib-ink); }

    /* Handset: one card per record, so no core column is ever scrolled off. */
    .cards { list-style: none; margin: 0; padding: 0; display: grid; }
    .card-row {
      display: grid; gap: var(--sp-2);
      padding: var(--sp-4); border-bottom: 1px solid var(--lib-line);
      grid-template-areas: "primary actions" "meta meta";
      grid-template-columns: 1fr auto;
    }
    .card-primary { grid-area: primary; font-weight: 600; }
    .card-actions { grid-area: actions; }
    .card-meta { grid-area: meta; margin: 0; display: grid; gap: 2px; }
    .card-meta div { display: flex; gap: var(--sp-2); align-items: baseline; }
    .card-meta dt {
      font-size: 11px; text-transform: uppercase; letter-spacing: .06em;
      color: var(--lib-ink-faint); min-width: 84px;
    }
    .card-meta dd { margin: 0; font-size: 13px; }
  `],
})
export class DataTable<T> {
  readonly columns = input.required<ColumnDef[]>();
  readonly rows = input.required<readonly T[]>();
  readonly meta = input<PageMeta | null>(null);
  readonly label = input('List');
  readonly sort = input<SortState | null>(null);
  readonly pageSizes = input<number[]>([15, 30, 50]);
  readonly trackBy = input<(row: T) => unknown>((row: T) => row);
  readonly rowClass = input<(row: T) => string>(() => '');

  readonly sorted = output<string>();
  readonly paged = output<PageEvent>();

  private readonly cells = contentChildren(CellTemplate);
  private readonly bp = inject(BreakpointObserver);

  readonly isHandset = toSignal(
    this.bp.observe([Breakpoints.Handset]).pipe(map(result => result.matches)),
    { initialValue: false },
  );

  readonly primaryColumn = computed(() => this.columns().find(c => c.primary) ?? null);
  readonly bareColumn = computed(() => this.columns().find(c => c.bare) ?? null);
  /** What the card layout lists under the title: everything but noise. */
  readonly cardColumns = computed(() =>
    this.columns().filter(c => !c.primary && !c.bare && !c.secondary));

  templateFor(key: string): TemplateRef<{ $implicit: T }> | null {
    const match = this.cells().find(cell => cell.libCell() === key);

    return (match?.template ?? null) as TemplateRef<{ $implicit: T }> | null;
  }

  indicator(key: string): string {
    const state = this.sort();

    return state?.field !== key ? '' : state.direction === 'asc' ? '↑' : '↓';
  }

  ariaSort(col: ColumnDef): string | null {
    const state = this.sort();

    if (!col.sortable) return null;

    return state?.field === col.key ? (state.direction === 'asc' ? 'ascending' : 'descending') : 'none';
  }
}
