import type { Meta, StoryObj } from '@storybook/angular';
import { DueStamp } from '../due-stamp';

const meta: Meta<DueStamp> = {
  title: 'Design System/DueStamp',
  component: DueStamp,
  tags: ['autodocs'],
  parameters: { docs: { description: { component:
    'Sello de fecha estilo ficha de préstamo. Elemento firma del sistema: monoespaciada, mayúsculas y ligera rotación de tampón.' } } },
};
export default meta;
type S = StoryObj<DueStamp>;

export const Activo: S =   { args: { status: 'active',   dueDate: '2026-07-24' } };
export const Vencido: S =  { args: { status: 'overdue',  dueDate: '2026-07-10', daysOverdue: 12 } };
export const VencidoUnDia: S = { args: { status: 'overdue', dueDate: '2026-07-21', daysOverdue: 1 } };
export const Devuelto: S = { args: { status: 'returned', dueDate: '2026-07-10', returnedAt: '2026-07-08' } };
