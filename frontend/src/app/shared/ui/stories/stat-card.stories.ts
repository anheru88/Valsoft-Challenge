import type { Meta, StoryObj } from '@storybook/angular';
import { StatCard } from '../stat-card';

const meta: Meta<StatCard> = { title: 'Shared UI/StatCard', component: StatCard, tags: ['autodocs'] };
export default meta;
type S = StoryObj<StatCard>;

export const Basic: S = { args: { label: 'Libros en catálogo', value: 1240 } };
export const WithHint: S = { args: { label: 'Prestados ahora', value: 520, hint: '15% del fondo' } };
export const Alert: S = { args: { label: 'Vencidos', value: 34, tone: 'alert', hint: 'Requieren seguimiento' } };
