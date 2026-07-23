import type { Meta, StoryObj } from '@storybook/angular';

/** The spacing scale used by cards, tables and dialogs. */
const meta: Meta = {
  title: 'Design System/Spacing',
  tags: ['autodocs'],
};
export default meta;

const STEPS = ['--sp-1', '--sp-2', '--sp-3', '--sp-4', '--sp-5', '--sp-6'];

export const Scale: StoryObj = {
  render: () => ({
    template: `
      <div style="display: grid; gap: 10px;">
        ${STEPS.map(step => `
          <div style="display: flex; align-items: center; gap: 12px;">
            <code style="font-family: var(--lib-font-mono); font-size: 12px; width: 76px;">${step}</code>
            <div style="height: 16px; width: var(${step}); background: var(--lib-green); border-radius: 3px;"></div>
          </div>`).join('')}
      </div>
    `,
  }),
};
