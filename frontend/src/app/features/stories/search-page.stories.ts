import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { SearchPage } from '../search/search-page';
import { asRole } from './page-harness';

/**
 * Global search. With no term it shows an invitation rather than an empty list:
 * the initial state and "no results" say different things.
 */
const meta: Meta<SearchPage> = {
  title: 'Pages/Search',
  component: SearchPage,
  decorators: [asRole('member')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<SearchPage>;

export const InitialState: S = { name: 'Initial state' };

export const NoResults: S = {
  name: 'No results',
  render: () => ({ props: { query: signal('zzzz'), results: signal([]) } }),
};
