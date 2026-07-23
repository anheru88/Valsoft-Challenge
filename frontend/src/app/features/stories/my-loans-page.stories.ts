import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { MyLoansPage } from '../loans/my-loans/my-loans-page';
import { Loan } from '../../core/models';
import { asRole } from './page-harness';

/** The member's view: what they have at home, and until when. */
const meta: Meta<MyLoansPage> = {
  title: 'Pages/My loans',
  component: MyLoansPage,
  decorators: [asRole('member')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<MyLoansPage>;

export const WithAnOverdue: S = { name: 'With an overdue loan' };

export const NoLoans: S = {
  name: 'No loans',
  render: () => ({ props: { loans: signal<Loan[]>([]) } }),
};
