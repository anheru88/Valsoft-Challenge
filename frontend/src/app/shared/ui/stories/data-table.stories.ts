import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata } from '@storybook/angular';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { DataTable, CellTemplate, type ColumnDef, type SortState } from '../data-table';
import type { PageMeta } from '../../../core/models';

interface Row { id: number; title: string; author: string; isbn: string; copies: string; }

const ROWS: Row[] = [
  { id: 1, title: 'One Hundred Years of Solitude', author: 'Gabriel García Márquez', isbn: '9780060883287', copies: '2 / 4' },
  { id: 2, title: 'The Aleph', author: 'Jorge Luis Borges', isbn: '9780142437889', copies: '0 / 3' },
  { id: 3, title: 'Pedro Páramo', author: 'Juan Rulfo', isbn: '9780802133908', copies: '1 / 1' },
];

const COLUMNS: ColumnDef[] = [
  { key: 'title', header: 'Title', sortable: true, primary: true },
  { key: 'author', header: 'Author', sortable: true },
  { key: 'isbn', header: 'ISBN', secondary: true },
  { key: 'copies', header: 'Available', align: 'end' },
  { key: 'actions', header: 'Actions', bare: true },
];

const META: PageMeta = { current_page: 1, per_page: 15, total: 42, last_page: 3 };
const SORT: SortState = { field: 'title', direction: 'asc' };

const meta: Meta<DataTable<Row>> = {
  title: 'Shared UI/DataTable',
  component: DataTable,
  tags: ['autodocs'],
  decorators: [moduleMetadata({ imports: [CellTemplate, MatButtonModule, MatIconModule] })],
  argTypes: { sorted: { action: 'sorted' }, paged: { action: 'paged' } },
  render: (args) => ({
    props: args,
    template: `
      <lib-data-table [columns]="columns" [rows]="rows" [meta]="meta" [sort]="sort"
                      [label]="label" [trackBy]="trackBy"
                      (sorted)="sorted($event)" (paged)="paged($event)">
        <ng-template libCell="title" let-row>{{ row.title }}</ng-template>
        <ng-template libCell="author" let-row>{{ row.author }}</ng-template>
        <ng-template libCell="isbn" let-row><span class="mono">{{ row.isbn }}</span></ng-template>
        <ng-template libCell="copies" let-row>{{ row.copies }}</ng-template>
        <ng-template libCell="actions" let-row>
          <button mat-icon-button [attr.aria-label]="'Edit ' + row.title">
            <mat-icon>edit</mat-icon>
          </button>
        </ng-template>
      </lib-data-table>`,
  }),
};
export default meta;
type S = StoryObj<DataTable<Row>>;

const base = {
  columns: COLUMNS,
  rows: ROWS,
  meta: META,
  sort: SORT,
  label: 'Catalogue',
  trackBy: (row: Row) => row.id,
};

export const WithData: S = { args: base };

export const Empty: S = { args: { ...base, rows: [], meta: { ...META, total: 0, last_page: 1 } } };

/** No paginator when the caller passes no meta — a fixed list, not a page. */
export const WithoutPagination: S = { args: { ...base, meta: null } };
