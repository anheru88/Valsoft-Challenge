import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { BookDetailPage } from '../books/detail/book-detail-page';
import { asRole } from './page-harness';

const meta: Meta<BookDetailPage> = {
  title: 'Pages/Libros/Detalle',
  component: BookDetailPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<BookDetailPage>;

export const Cargado: S = {};

export const Cargando: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Error: S = { render: () => ({ props: { error: signal(true) } }) };

/** Sin ejemplares libres: la acción de prestar debe quedar fuera de alcance. */
export const SinEjemplares: S = {
  name: 'Sin ejemplares',
  render: () => ({
    props: {
      book: signal({
        id: 1, title: 'Cien años de soledad', isbn: '9780307474728',
        description: 'Crónica de los Buendía en Macondo.',
        publisher: 'Vintage', publication_year: 1967,
        total_copies: 3, available_copies: 0, is_available: false,
        authors: [{ id: 3, name: 'Gabriel García Márquez' }],
        categories: [{ id: 1, name: 'Narrativa', slug: 'narrativa' }],
        active_loans_count: 3, created_at: '2026-06-01',
      }),
    },
  }),
};

/** Un socio ve la ficha sin las acciones de curaduría ni el recuento de préstamos. */
export const ComoSocio: S = { name: 'Como socio', decorators: [asRole('member')] };
