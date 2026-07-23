import type { Meta, StoryObj } from '@storybook/angular';
import { EmptyState } from '../empty-state';

const meta: Meta<EmptyState> = { title: 'Design System/EmptyState', component: EmptyState, tags: ['autodocs'] };
export default meta;
type S = StoryObj<EmptyState>;

export const CatalogoVacio: S = { args: {
  icon: 'menu_book', title: 'Aún no hay libros',
  message: 'Añade tu primer libro para empezar a construir el catálogo.',
  actionLabel: 'Añadir libro',
} };
export const SinResultadosDeFiltro: S = { args: {
  icon: 'filter_alt_off', title: 'Sin resultados con estos filtros',
  message: 'Prueba a ampliar la búsqueda o limpia los filtros activos.',
  actionLabel: 'Limpiar filtros',
} };
