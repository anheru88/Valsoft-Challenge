import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { AuthorsListPage } from '../authors/authors-list-page';
import { Author } from '../../core/models';
import { asRole } from './page-harness';

/**
 * Autores: alta y edición ocurren en diálogo, así que la página solo tiene tres
 * estados propios — cargando, con datos y vacía.
 */
const meta: Meta<AuthorsListPage> = {
  title: 'Pages/Autores',
  component: AuthorsListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<AuthorsListPage>;

export const Cargado: S = {};

export const Cargando: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Vacio: S = {
  name: 'Vacío',
  render: () => ({ props: { authors: signal<Author[]>([]) } }),
};
