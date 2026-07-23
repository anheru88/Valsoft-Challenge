import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { UsersListPage } from '../users/list/users-list-page';
import { User } from '../../core/models';
import { asRole } from './page-harness';

const meta: Meta<UsersListPage> = {
  title: 'Pages/Users/List',
  component: UsersListPage,
  decorators: [asRole('admin')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<UsersListPage>;

export const Loaded: S = {};

export const FilteredByRole: S = {
  name: 'Filtered by role',
  render: () => ({ props: { roleFilter: signal('librarian') } }),
};

export const NoResults: S = {
  name: 'No results',
  render: () => ({ props: { users: signal<User[]>([]), q: signal('nadie') } }),
};
