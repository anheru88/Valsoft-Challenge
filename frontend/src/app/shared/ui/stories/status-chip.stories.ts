import type { Meta, StoryObj } from '@storybook/angular';
import { StatusChip } from '../status-chip';

const meta: Meta<StatusChip> = { title: 'Shared UI/StatusChip', component: StatusChip, tags: ['autodocs'] };
export default meta;
type S = StoryObj<StatusChip>;

export const Active: S =   { args: { status: 'active' } };
export const Overdue: S =  { args: { status: 'overdue' } };
export const Returned: S = { args: { status: 'returned' } };
