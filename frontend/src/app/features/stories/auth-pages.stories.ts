import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { LoginPage } from '../auth/login/login-page';
import { asRole } from './page-harness';

/**
 * Sign-in. A credential failure is a business error and belongs next to the
 * form, never in a floating toast (PRD 5).
 */
const meta: Meta<LoginPage> = {
  title: 'Pages/Auth/Sign in',
  component: LoginPage,
  decorators: [asRole('member')],
  parameters: { layout: 'centered' },
};
export default meta;

type S = StoryObj<LoginPage>;

export const Blank: S = { name: 'Blank form' };

export const InvalidCredentials: S = {
  name: 'Invalid credentials',
  render: () => ({ props: { errorMessage: signal('That email and password do not match.') } }),
};

export const ExpiredSession: S = {
  name: 'Expired session',
  render: () => ({ props: { sessionExpired: signal(true) } }),
};

export const Submitting: S = {
  name: 'Submitting',
  render: () => ({ props: { loading: signal(true) } }),
};
