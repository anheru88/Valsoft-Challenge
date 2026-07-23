import { applicationConfig, type Preview } from '@storybook/angular';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';

/* Global styles (tokens, base layer, Material theme) come from the Angular build
   target this preview reuses, plus .storybook/preview.scss — see angular.json.
   Importing SCSS here would bypass Angular's style pipeline. */

/**
 * Mirrors what the topbar does in the application: dark mode is a class on
 * <body>, and every component inherits it through the tokens.
 */
function applyTheme(theme: string): void {
  document.body.classList.toggle('dark-theme', theme === 'dark');
}

const preview: Preview = {
  decorators: [
    // Material dialogs, snackbars and expansion panels need the animations
    // provider; supplying it once here keeps every story free of boilerplate.
    applicationConfig({ providers: [provideAnimationsAsync()] }),
    (story, context) => {
      applyTheme(context.globals['theme'] as string);

      return story();
    },
  ],
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
    a11y: { test: 'todo' },
    backgrounds: { disable: true },
    docs: { toc: true },
  },
  globalTypes: {
    theme: {
      description: 'Light or dark, the way the topbar toggle switches it',
      defaultValue: 'light',
      toolbar: {
        title: 'Tema',
        icon: 'circlehollow',
        items: [
          { value: 'light', icon: 'sun', title: 'Claro' },
          { value: 'dark', icon: 'moon', title: 'Oscuro' },
        ],
        dynamicTitle: true,
      },
    },
  },
};

export default preview;
