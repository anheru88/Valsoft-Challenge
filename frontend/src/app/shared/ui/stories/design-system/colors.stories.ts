import type { Meta, StoryObj } from '@storybook/angular';

/**
 * La paleta, leída de los tokens. Ninguna feature escribe un color literal: si
 * un valor cambia aquí, cambia en todo el sistema, incluido el modo oscuro.
 */
const meta: Meta = {
  title: 'Design System/Colores',
  tags: ['autodocs'],
  parameters: {
    docs: { description: { component: 'Superficies, tinta y marca. Cambia el tema en la barra superior para ver los mismos tokens en oscuro.' } },
  },
};
export default meta;

type Swatch = { token: string; name: string; note?: string };

const GROUPS: { group: string; swatches: Swatch[] }[] = [
  {
    group: 'Superficies',
    swatches: [
      { token: '--lib-paper', name: 'paper', note: 'fondo de la aplicación' },
      { token: '--lib-surface', name: 'surface', note: 'tarjetas y tablas' },
      { token: '--lib-surface-2', name: 'surface-2', note: 'hover de fila, chips neutros' },
      { token: '--lib-line', name: 'line', note: 'bordes hairline' },
    ],
  },
  {
    group: 'Tinta',
    swatches: [
      { token: '--lib-ink', name: 'ink', note: 'texto principal' },
      { token: '--lib-ink-soft', name: 'ink-soft', note: 'texto secundario' },
      { token: '--lib-ink-faint', name: 'ink-faint', note: 'metadatos' },
    ],
  },
  {
    group: 'Marca',
    swatches: [
      { token: '--lib-green', name: 'green', note: 'primario' },
      { token: '--lib-green-strong', name: 'green-strong', note: 'préstamo activo' },
      { token: '--lib-brass', name: 'brass', note: 'acento' },
      { token: '--lib-stamp', name: 'stamp', note: 'vencidos y acciones destructivas' },
    ],
  },
];

const template = `
  <div style="display: grid; gap: 28px;">
    ${GROUPS.map(({ group, swatches }) => `
      <section>
        <h3 style="font-family: var(--lib-font-display); margin: 0 0 12px;">${group}</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px;">
          ${swatches.map(s => `
            <div style="border: 1px solid var(--lib-line); border-radius: 10px; overflow: hidden; background: var(--lib-surface);">
              <div style="height: 64px; background: var(${s.token});"></div>
              <div style="padding: 10px 12px;">
                <div style="font-family: var(--lib-font-mono); font-size: 12px;">${s.token}</div>
                ${s.note ? `<div style="font-size: 12px; color: var(--lib-ink-soft); margin-top: 2px;">${s.note}</div>` : ''}
              </div>
            </div>`).join('')}
        </div>
      </section>`).join('')}
  </div>
`;

export const Paleta: StoryObj = { render: () => ({ template }) };
