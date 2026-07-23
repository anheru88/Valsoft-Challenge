import type { Meta, StoryObj } from '@storybook/angular';

/**
 * The palette, read from the tokens. No feature writes a literal colour: change
 * a value here and it changes system-wide, dark mode included.
 */
const meta: Meta = {
  title: 'Design System/Colours',
  tags: ['autodocs'],
  parameters: {
    docs: { description: { component: 'Surfaces, ink and brand. Flip the theme in the toolbar to see the same tokens in dark.' } },
  },
};
export default meta;

type Swatch = { token: string; name: string; note?: string };

const GROUPS: { group: string; swatches: Swatch[] }[] = [
  {
    group: 'Surfaces',
    swatches: [
      { token: '--lib-paper', name: 'paper', note: 'application background' },
      { token: '--lib-surface', name: 'surface', note: 'cards and tables' },
      { token: '--lib-surface-2', name: 'surface-2', note: 'row hover, neutral chips' },
      { token: '--lib-line', name: 'line', note: 'hairline borders' },
    ],
  },
  {
    group: 'Ink',
    swatches: [
      { token: '--lib-ink', name: 'ink', note: 'primary text' },
      { token: '--lib-ink-soft', name: 'ink-soft', note: 'secondary text' },
      { token: '--lib-ink-faint', name: 'ink-faint', note: 'metadata' },
    ],
  },
  {
    group: 'Brand',
    swatches: [
      { token: '--lib-green', name: 'green', note: 'primary' },
      { token: '--lib-green-strong', name: 'green-strong', note: 'active loan' },
      { token: '--lib-brass', name: 'brass', note: 'accent' },
      { token: '--lib-stamp', name: 'stamp', note: 'overdue and destructive actions' },
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

export const Palette: StoryObj = { render: () => ({ template }) };
