import type { Meta, StoryObj } from '@storybook/angular';
import { InlineAlert } from '../inline-alert';

const meta: Meta<InlineAlert> = {
  title: 'Design System/InlineAlert',
  component: InlineAlert,
  tags: ['autodocs'],
  render: (args) => ({
    props: args,
    template: `<lib-inline-alert [tone]="tone">{{ text }}</lib-inline-alert>`,
  }),
};
export default meta;
type S = StoryObj<InlineAlert & { text: string }>;

export const ReglaDeNegocio: S = { args: { tone: 'danger',
  text: 'Este socio ya tiene 5 préstamos activos (límite 5). Debe devolver un libro antes de llevarse otro.' } as any };
export const AvisoVencidos: S = { args: { tone: 'warn',
  text: 'El socio tiene 2 préstamos vencidos. No puede llevarse libros hasta devolverlos.' } as any };
export const Informativo: S = { args: { tone: 'info',
  text: 'La fecha de devolución por defecto es de 14 días (máximo 60).' } as any };
