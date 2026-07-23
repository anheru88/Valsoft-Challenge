import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { LoansListPage } from '../loans/list/loans-list-page';
import { Loan } from '../../core/models';
import { asRole } from './page-harness';

/**
 * The circulation list. A check-in is not destructive, so it takes one click and
 * a toast rather than a confirmation dialog (PRD 5).
 */
const meta: Meta<LoansListPage> = {
  title: 'Pages/Loans/List',
  component: LoansListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<LoansListPage>;

export const Loaded: S = {};

export const Loading: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Error: S = { render: () => ({ props: { error: signal(true) } }) };

export const NoLoans: S = {
  name: 'No loans',
  render: () => ({
    props: {
      allLoans: signal<Loan[]>([]),
      meta: signal({ current_page: 1, per_page: 15, total: 0, last_page: 1 }),
    },
  }),
};

/** Check-in in flight: that row's button is busy, the others are not. */
export const CheckInInFlight: S = {
  name: 'Check-in in flight',
  render: () => ({ props: { returningId: signal(501) } }),
};
