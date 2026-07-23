import { signal } from '@angular/core';
import type { Meta, StoryObj } from '@storybook/angular';
import { LoginPage } from '../auth/login/login-page';
import { asRole } from './page-harness';

/**
 * El acceso. Un fallo de credenciales es un error de negocio y va junto al
 * formulario, nunca en un aviso flotante (PRD 5).
 */
const meta: Meta<LoginPage> = {
  title: 'Pages/Acceso/Entrar',
  component: LoginPage,
  decorators: [asRole('member')],
  parameters: { layout: 'centered' },
};
export default meta;

type S = StoryObj<LoginPage>;

export const Vacio: S = { name: 'Formulario vacío' };

export const CredencialesInvalidas: S = {
  name: 'Credenciales inválidas',
  render: () => ({ props: { errorMessage: signal('Estas credenciales no coinciden con nuestros registros.') } }),
};

export const SesionCaducada: S = {
  name: 'Sesión caducada',
  render: () => ({ props: { sessionExpired: signal(true) } }),
};

export const Enviando: S = {
  name: 'Enviando',
  render: () => ({ props: { loading: signal(true) } }),
};
