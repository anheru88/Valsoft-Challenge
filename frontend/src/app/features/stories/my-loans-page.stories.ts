import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { MyLoansPage } from '../loans/my-loans/my-loans-page';
import { Loan } from '../../core/models';
import { asRole } from './page-harness';

/** La vista del socio: qué tiene en casa y para cuándo. */
const meta: Meta<MyLoansPage> = {
  title: 'Pages/Mis préstamos',
  component: MyLoansPage,
  decorators: [asRole('member')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<MyLoansPage>;

export const ConVencido: S = { name: 'Con un vencido' };

export const SinPrestamos: S = {
  name: 'Sin préstamos',
  render: () => ({ props: { loans: signal<Loan[]>([]) } }),
};
