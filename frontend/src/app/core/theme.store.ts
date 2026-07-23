import { Injectable, computed, signal } from '@angular/core';

const THEME_KEY = 'librarium.theme';

export type Theme = 'light' | 'dark';

/**
 * Which theme is on, and remembering it.
 *
 * The choice is applied as a class on `<body>`, so every component inherits it
 * through the tokens rather than reading a flag (PRD 7). Reading it back at
 * start-up is the whole point: a preference that has to be re-picked on every
 * reload is not a preference.
 *
 * With nothing stored, the operating system decides — someone who runs their
 * desk dark should not be handed a white screen first.
 */
@Injectable({ providedIn: 'root' })
export class ThemeStore {
  private readonly _theme = signal<Theme>(readStoredTheme() ?? systemTheme());

  readonly theme = this._theme.asReadonly();
  readonly isDark = computed(() => this._theme() === 'dark');

  constructor() {
    this.apply();
  }

  toggle(): void {
    this.set(this.isDark() ? 'light' : 'dark');
  }

  set(theme: Theme): void {
    this._theme.set(theme);
    localStorage.setItem(THEME_KEY, theme);
    this.apply();
  }

  private apply(): void {
    document.body.classList.toggle('dark-theme', this.isDark());
  }
}

function readStoredTheme(): Theme | null {
  const stored = localStorage.getItem(THEME_KEY);

  return stored === 'dark' || stored === 'light' ? stored : null;
}

function systemTheme(): Theme {
  return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}
