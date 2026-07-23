import type { Meta, StoryObj } from '@storybook/angular';
import { PageHeader } from '../page-header';

const meta: Meta<PageHeader> = {
  title: 'Shared UI/PageHeader',
  component: PageHeader,
  tags: ['autodocs'],
  render: (args) => ({
    props: args,
    template: `
      <lib-page-header [title]="title" [subtitle]="subtitle">
        <button mat-flat-button color="primary">Añadir libro</button>
      </lib-page-header>`,
  }),
};
export default meta;
type S = StoryObj<PageHeader>;

export const WithAction: S = { args: { title: 'Libros', subtitle: '1.240 títulos en catálogo' } };
export const TitleOnly: S = { args: { title: 'Mis préstamos' } };
