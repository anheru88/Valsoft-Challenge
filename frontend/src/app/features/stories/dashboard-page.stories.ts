import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { DashboardPage } from '../dashboard/dashboard-page';
import { asRole } from './page-harness';

/**
 * The desk dashboard. The KPIs load separately from the other widgets, so their
 * skeleton and their error are states of their own: a dashboard failure never
 * blocks circulation (PRD 9, availability).
 */
const meta: Meta<DashboardPage> = {
  title: 'Pages/Dashboard',
  component: DashboardPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<DashboardPage>;

export const Loaded: S = {};

export const LoadingKpis: S = {
  name: 'Loading KPIs',
  render: () => ({ props: { loadingKpis: signal(true) } }),
};

export const KpiError: S = {
  name: 'KPI error',
  render: () => ({ props: { kpisError: signal(true) } }),
};

/** A library that has just opened: nothing to report yet. */
export const NoActivity: S = {
  name: 'No activity',
  render: () => ({
    props: {
      kpis: signal({
        total_books: 0, total_copies: 0, available_copies: 0, borrowed_now: 0,
        overdue_now: 0, total_members: 0, books_added_this_month: 0,
        loans_this_month: 0, returns_this_month: 0,
      }),
      activity: signal([]),
      byCategory: signal([]),
      popularAuthors: signal([]),
    },
  }),
};
