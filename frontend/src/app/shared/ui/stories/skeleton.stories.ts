import type { Meta, StoryObj } from '@storybook/angular';
import { Skeleton } from '../skeleton';

const meta: Meta<Skeleton> = { title: 'Shared UI/Skeleton', component: Skeleton, tags: ['autodocs'] };
export default meta;
type S = StoryObj<Skeleton>;

export const FilasDeTabla: S = { args: { variant: 'rows', rows: 5 } };
export const TarjetaKpi: S =   { args: { variant: 'stat' } };
export const TarjetaDetalle: S = { args: { variant: 'card' } };
