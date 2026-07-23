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
        <button mat-flat-button color="primary">Add book</button>
      </lib-page-header>`,
  }),
};
export default meta;
type S = StoryObj<PageHeader>;

export const WithAction: S = { args: { title: 'Books', subtitle: '1,240 titles in the catalogue' } };
export const TitleOnly: S = { args: { title: 'My loans' } };
