import type { Meta, StoryObj } from '@storybook/angular';
import { SearchInput } from '../search-input';

const meta: Meta<SearchInput> = {
  title: 'Design System/SearchInput',
  component: SearchInput,
  tags: ['autodocs'],
  argTypes: { changed: { action: 'changed (300ms debounce)' } },
};
export default meta;
type S = StoryObj<SearchInput>;

export const Basico: S = { args: { placeholder: 'Buscar por título, ISBN, autor…' } };
export const ConValorInicial: S = { args: { placeholder: 'Buscar autores…', initial: 'garcía' } };
