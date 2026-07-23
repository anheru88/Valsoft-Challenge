import type { Meta, StoryObj } from '@storybook/angular';
import { InlineAlert } from '../inline-alert';

const meta: Meta<InlineAlert> = {
  title: 'Shared UI/InlineAlert',
  component: InlineAlert,
  tags: ['autodocs'],
  render: (args) => ({
    props: args,
    template: `<lib-inline-alert [tone]="tone">{{ text }}</lib-inline-alert>`,
  }),
};
export default meta;
type S = StoryObj<InlineAlert & { text: string }>;

export const BusinessRule: S = { args: { tone: 'danger',
  text: 'This member already holds 5 active loans (the limit). They must return one before taking another.' } as any };
export const OverdueWarning: S = { args: { tone: 'warn',
  text: 'This member has 2 overdue loans. They cannot borrow again until those come back.' } as any };
export const Informational: S = { args: { tone: 'info',
  text: 'The default due date is 14 days away, 60 at most.' } as any };
