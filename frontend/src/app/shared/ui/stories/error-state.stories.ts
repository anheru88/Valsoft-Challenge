import type { Meta, StoryObj } from '@storybook/angular';
import { ErrorState } from '../error-state';

const meta: Meta<ErrorState> = { title: 'Shared UI/ErrorState', component: ErrorState, tags: ['autodocs'] };
export default meta;
type S = StoryObj<ErrorState>;

export const LoadFailure: S = { args: {
  title: 'Could not load the loans',
  message: 'Comprueba tu conexión y vuelve a intentarlo.',
  traceId: '8c9f1e2a-4b21-77aa-90ff-1d2e3f4a5b6c',
} };
