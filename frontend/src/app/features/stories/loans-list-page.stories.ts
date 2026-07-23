import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { LoansListPage } from '../loans/list/loans-list-page';
import { Loan } from '../../core/models';
import { asRole } from './page-harness';

/**
 * El listado de circulación. La devolución no es destructiva, así que se
 * confirma con un clic y un aviso, sin diálogo de por medio (PRD 5).
 */
const meta: Meta<LoansListPage> = {
  title: 'Pages/Préstamos/Listado',
  component: LoansListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<LoansListPage>;

export const Cargado: S = {};

export const Cargando: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Error: S = { render: () => ({ props: { error: signal(true) } }) };

export const SinPrestamos: S = {
  name: 'Sin préstamos',
  render: () => ({
    props: {
      allLoans: signal<Loan[]>([]),
      meta: signal({ current_page: 1, per_page: 15, total: 0, last_page: 1 }),
    },
  }),
};

/** Devolución en curso: el botón de esa fila queda ocupado, el resto no. */
export const DevolucionEnCurso: S = {
  name: 'Devolución en curso',
  render: () => ({ props: { returningId: signal(501) } }),
};
