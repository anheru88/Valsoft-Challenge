import type { Meta, StoryObj } from '@storybook/angular';
import { SearchInput } from '../search-input';

const meta: Meta<SearchInput> = {
  title: 'Shared UI/SearchInput',
  component: SearchInput,
  tags: ['autodocs'],
  argTypes: { changed: { action: 'changed (300ms debounce)' } },
};
export default meta;
type S = StoryObj<SearchInput>;

export const Basic: S = { args: { placeholder: 'Search by title, ISBN or author…' } };
export const WithInitialValue: S = { args: { placeholder: 'Search authors…', initial: 'garcía' } };
