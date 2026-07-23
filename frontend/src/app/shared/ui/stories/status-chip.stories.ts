import type { Meta, StoryObj } from '@storybook/angular';
import { StatusChip } from '../status-chip';

const meta: Meta<StatusChip> = { title: 'Shared UI/StatusChip', component: StatusChip, tags: ['autodocs'] };
export default meta;
type S = StoryObj<StatusChip>;

export const Activo: S =   { args: { status: 'active' } };
export const Vencido: S =  { args: { status: 'overdue' } };
export const Devuelto: S = { args: { status: 'returned' } };
