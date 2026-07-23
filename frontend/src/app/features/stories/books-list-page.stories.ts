import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { BooksListPage } from '../books/list/books-list-page';
import { Book } from '../../core/models';
import { asRole } from './page-harness';

/**
 * The catalogue, the most visited screen in the system. These stories walk the
 * four states PRD 5 requires of any list: loading with a skeleton, loaded,
 * empty — telling an empty catalogue apart from a filter with no results — and
 * error with retry.
 */
const meta: Meta<BooksListPage> = {
  title: 'Pages/Books/List',
  component: BooksListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<BooksListPage>;

const empty = { books: signal<Book[]>([]), meta: signal({ current_page: 1, per_page: 15, total: 0, last_page: 1 }) };

export const Loaded: S = {};

export const Loading: S = {
  render: () => ({ props: { loading: signal(true) } }),
};

export const EmptyCatalogue: S = {
  name: 'Empty catalogue',
  render: () => ({ props: { ...empty } }),
};

export const NoFilterResults: S = {
  name: 'No results for these filters',
  render: () => ({
    props: {
      ...empty,
      filters: signal({ q: 'quijote', categoryId: null, onlyAvailable: false }),
    },
  }),
};

export const Error: S = {
  render: () => ({ props: { error: signal(true) } }),
};

/** A member sees the same catalogue without the curation actions. */
export const AsMember: S = {
  name: 'As a member',
  decorators: [asRole('member')],
};
