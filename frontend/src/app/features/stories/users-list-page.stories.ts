import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { UsersListPage } from '../users/list/users-list-page';
import { User } from '../../core/models';
import { asRole } from './page-harness';

const meta: Meta<UsersListPage> = {
  title: 'Pages/Usuarios/Listado',
  component: UsersListPage,
  decorators: [asRole('admin')],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<UsersListPage>;

export const Cargado: S = {};

export const FiltradoPorRol: S = {
  name: 'Filtrado por rol',
  render: () => ({ props: { roleFilter: signal('librarian') } }),
};

export const SinResultados: S = {
  name: 'Sin resultados',
  render: () => ({ props: { users: signal<User[]>([]), q: signal('nadie') } }),
};
