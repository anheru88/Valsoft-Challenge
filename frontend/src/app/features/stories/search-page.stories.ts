import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { SearchPage } from '../search/search-page';
import { asRole } from './page-harness';

/**
 * La búsqueda global. Sin término no muestra una lista vacía sino una
 * invitación: el estado inicial y el "sin resultados" dicen cosas distintas.
 */
const meta: Meta<SearchPage> = {
  title: 'Pages/Búsqueda',
  component: SearchPage,
  decorators: [asRole('member')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<SearchPage>;

export const EstadoInicial: S = { name: 'Estado inicial' };

export const SinResultados: S = {
  name: 'Sin resultados',
  render: () => ({ props: { query: signal('zzzz'), results: signal([]) } }),
};
