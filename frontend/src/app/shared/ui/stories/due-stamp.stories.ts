import type { Meta, StoryObj } from '@storybook/angular';
import { DueStamp } from '../due-stamp';

const meta: Meta<DueStamp> = {
  title: 'Shared UI/DueStamp',
  component: DueStamp,
  tags: ['autodocs'],
  parameters: { docs: { description: { component:
    'A due-date stamp in the style of a loan card. The system\'s signature element: monospaced, uppercase, with a slight inked rotation.' } } },
};
export default meta;
type S = StoryObj<DueStamp>;

export const Active: S =   { args: { status: 'active',   dueDate: '2026-07-24' } };
export const Overdue: S =  { args: { status: 'overdue',  dueDate: '2026-07-10', daysOverdue: 12 } };
export const OverdueByOneDay: S = { args: { status: 'overdue', dueDate: '2026-07-21', daysOverdue: 1 } };
export const Returned: S = { args: { status: 'returned', dueDate: '2026-07-10', returnedAt: '2026-07-08' } };
