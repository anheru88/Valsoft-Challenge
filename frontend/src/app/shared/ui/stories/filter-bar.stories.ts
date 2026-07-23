import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata } from '@storybook/angular';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { FilterBar } from '../filter-bar';
import { SearchInput } from '../search-input';

const meta: Meta<FilterBar> = {
  title: 'Shared UI/FilterBar',
  component: FilterBar,
  tags: ['autodocs'],
  decorators: [
    moduleMetadata({
      imports: [SearchInput, MatFormFieldModule, MatInputModule, MatSelectModule, MatCheckboxModule],
    }),
  ],
  argTypes: { cleared: { action: 'cleared' } },
  render: (args) => ({
    props: args,
    template: `
      <lib-filter-bar [label]="label" [active]="active" (cleared)="cleared()">
        <lib-search-input placeholder="Title, ISBN or author…" />
        <mat-form-field appearance="outline" style="min-width: 200px">
          <mat-label>Category</mat-label>
          <mat-select><mat-option>Fiction</mat-option></mat-select>
        </mat-form-field>
        <mat-checkbox>Available only</mat-checkbox>
      </lib-filter-bar>`,
  }),
};
export default meta;
type S = StoryObj<FilterBar>;

/** Untouched list: no way out is offered, because there is nothing to leave. */
export const Idle: S = { args: { label: 'Catalogue filters', active: false } };

export const Filtering: S = { args: { label: 'Catalogue filters', active: true } };

export const CustomClearLabel: S = {
  args: { label: 'Loan filters', active: true, clearLabel: 'Reset' },
};
