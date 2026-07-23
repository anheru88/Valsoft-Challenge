import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { BookDetailPage } from '../books/detail/book-detail-page';
import { asRole } from './page-harness';

const meta: Meta<BookDetailPage> = {
  title: 'Pages/Books/Detail',
  component: BookDetailPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<BookDetailPage>;

export const Loaded: S = {};

export const Loading: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Error: S = { render: () => ({ props: { error: signal(true) } }) };

/** No free copies: the lend action must be out of reach. */
export const NoCopiesLeft: S = {
  name: 'No copies left',
  render: () => ({
    props: {
      book: signal({
        id: 1, title: 'One Hundred Years of Solitude', isbn: '9780307474728',
        description: 'The Buendía family across a century in Macondo.',
        publisher: 'Vintage', publication_year: 1967,
        total_copies: 3, available_copies: 0, is_available: false,
        authors: [{ id: 3, name: 'Gabriel García Márquez' }],
        categories: [{ id: 1, name: 'Fiction', slug: 'fiction' }],
        active_loans_count: 3, created_at: '2026-06-01',
      }),
    },
  }),
};

/** A member sees the record without curation actions or the loan count. */
export const AsMember: S = { name: 'As a member', decorators: [asRole('member')] };
