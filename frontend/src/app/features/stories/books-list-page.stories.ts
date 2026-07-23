import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { BooksListPage } from '../books/list/books-list-page';
import { Book } from '../../core/models';
import { asRole } from './page-harness';

/**
 * El catálogo, la pantalla más visitada del sistema. Las historias recorren los
 * cuatro estados que el PRD 5 exige de cualquier listado: cargando con
 * esqueleto, cargado, vacío —distinguiendo catálogo vacío de filtro sin
 * resultados— y error con reintento.
 */
const meta: Meta<BooksListPage> = {
  title: 'Pages/Libros/Listado',
  component: BooksListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<BooksListPage>;

const empty = { books: signal<Book[]>([]), meta: signal({ current_page: 1, per_page: 15, total: 0, last_page: 1 }) };

export const Cargado: S = {};

export const Cargando: S = {
  render: () => ({ props: { loading: signal(true) } }),
};

export const CatalogoVacio: S = {
  name: 'Catálogo vacío',
  render: () => ({ props: { ...empty } }),
};

export const SinResultadosDeFiltro: S = {
  name: 'Sin resultados de filtro',
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

/** Un socio ve el mismo catálogo sin las acciones de curaduría. */
export const ComoSocio: S = {
  name: 'Como socio',
  decorators: [asRole('member')],
};
