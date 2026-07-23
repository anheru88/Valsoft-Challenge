import type { Meta, StoryObj } from '@storybook/angular';

/**
 * Tres familias con un reparto deliberado: Fraunces para títulos, Public Sans
 * para la interfaz, e IBM Plex Mono para el dato "de ficha" — ISBN, fechas y
 * códigos — que es lo que un bibliotecario compara carácter a carácter.
 */
const meta: Meta = {
  title: 'Design System/Tipografía',
  tags: ['autodocs'],
};
export default meta;

export const Escala: StoryObj = {
  render: () => ({
    template: `
      <div style="display: grid; gap: 20px; max-width: 720px;">
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-display · Fraunces</div>
          <h1 style="font-family: var(--lib-font-display); font-size: 32px; margin: 4px 0;">Cien años de soledad</h1>
          <h2 style="font-family: var(--lib-font-display); font-size: 24px; margin: 4px 0;">Panel de la biblioteca</h2>
        </div>
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-body · Public Sans</div>
          <p style="margin: 4px 0;">Texto de interfaz a 14px, el tamaño de las tablas y los formularios.</p>
          <p style="margin: 4px 0; color: var(--lib-ink-soft); font-size: 13px;">Texto secundario para metadatos y ayudas.</p>
        </div>
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-mono · IBM Plex Mono</div>
          <p style="font-family: var(--lib-font-mono); margin: 4px 0;">ISBN 978-0-06-088328-7</p>
          <p style="font-family: var(--lib-font-mono); margin: 4px 0;">Vence 2026-07-24 · 12 días de retraso</p>
        </div>
      </div>
    `,
  }),
};
