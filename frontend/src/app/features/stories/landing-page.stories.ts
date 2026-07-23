import { provideRouter } from '@angular/router';
import { applicationConfig, type Meta, type StoryObj } from '@storybook/angular';
import { LandingPage } from '../landing/landing-page';

/**
 * The public front door — the only screen a visitor sees before signing in.
 *
 * It needs no session, only a router for its links, so it is the one page story
 * that carries no mocked account.
 */
const meta: Meta<LandingPage> = {
  title: 'Pages/Landing',
  component: LandingPage,
  decorators: [applicationConfig({ providers: [provideRouter([{ path: '**', children: [] }])] })],
  parameters: { layout: 'fullscreen' },
};
export default meta;

type S = StoryObj<LandingPage>;

export const Default: S = {};

/**
 * The brand green flips to a light mint in dark mode, so anything sitting on it
 * takes the page colour as its foreground rather than a literal white.
 */
export const Dark: S = {
  globals: { theme: 'dark' },
};
