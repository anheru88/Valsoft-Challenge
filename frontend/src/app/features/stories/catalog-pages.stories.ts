import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { AuthorsListPage } from '../authors/authors-list-page';
import { Author } from '../../core/models';
import { asRole } from './page-harness';

/**
 * Authors: creating and editing happen in a dialog, so the page itself has only
 * three states — loading, populated and empty.
 */
const meta: Meta<AuthorsListPage> = {
  title: 'Pages/Authors',
  component: AuthorsListPage,
  decorators: [asRole('librarian')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<AuthorsListPage>;

export const Loaded: S = {};

export const Loading: S = { render: () => ({ props: { loading: signal(true) } }) };

export const Empty: S = {
  name: 'Empty',
  render: () => ({ props: { authors: signal<Author[]>([]) } }),
};
