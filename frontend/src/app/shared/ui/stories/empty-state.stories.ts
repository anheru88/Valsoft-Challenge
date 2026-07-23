import type { Meta, StoryObj } from '@storybook/angular';
import { EmptyState } from '../empty-state';

const meta: Meta<EmptyState> = { title: 'Shared UI/EmptyState', component: EmptyState, tags: ['autodocs'] };
export default meta;
type S = StoryObj<EmptyState>;

export const EmptyCatalogue: S = { args: {
  icon: 'menu_book', title: 'No books yet',
  message: 'Add your first book and start building the catalogue.',
  actionLabel: 'Add book',
} };
export const NoFilterResults: S = { args: {
  icon: 'filter_alt_off', title: 'No results for these filters',
  message: 'Try widening the search, or clear the active filters.',
  actionLabel: 'Limpiar filtros',
} };
