import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { DashboardPage } from '../dashboard/dashboard-page';
import { asRole } from './page-harness';

/**
 * El panel de mostrador. Los KPI cargan por separado del resto de widgets, así
 * que su esqueleto y su error son estados propios: un fallo del panel nunca
 * bloquea la circulación (PRD 9, disponibilidad).
 */
const meta: Meta<DashboardPage> = {
  title: 'Pages/Panel',
  component: DashboardPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<DashboardPage>;

export const Cargado: S = {};

export const CargandoKpis: S = {
  name: 'Cargando KPIs',
  render: () => ({ props: { loadingKpis: signal(true) } }),
};

export const ErrorDeKpis: S = {
  name: 'Error de KPIs',
  render: () => ({ props: { kpisError: signal(true) } }),
};

/** Biblioteca recién puesta en marcha: nada que contar todavía. */
export const SinActividad: S = {
  name: 'Sin actividad',
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
