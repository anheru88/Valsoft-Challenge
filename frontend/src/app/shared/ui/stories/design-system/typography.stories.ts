import type { Meta, StoryObj } from '@storybook/angular';

/**
 * Three families with a deliberate split: Fraunces for headings, Public Sans for
 * the interface, and IBM Plex Mono for catalogue-card data — ISBNs, dates and
 * codes — which is what a librarian compares character by character.
 */
const meta: Meta = {
  title: 'Design System/Typography',
  tags: ['autodocs'],
};
export default meta;

export const Scale: StoryObj = {
  render: () => ({
    template: `
      <div style="display: grid; gap: 20px; max-width: 720px;">
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-display · Fraunces</div>
          <h1 style="font-family: var(--lib-font-display); font-size: 32px; margin: 4px 0;">Cien años de soledad</h1>
          <h2 style="font-family: var(--lib-font-display); font-size: 24px; margin: 4px 0;">Library dashboard</h2>
        </div>
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-body · Public Sans</div>
          <p style="margin: 4px 0;">Interface text at 14px, the size tables and forms use.</p>
          <p style="margin: 4px 0; color: var(--lib-ink-soft); font-size: 13px;">Secondary text for metadata and hints.</p>
        </div>
        <div>
          <div style="font-family: var(--lib-font-mono); font-size: 11px; color: var(--lib-ink-faint);">--lib-font-mono · IBM Plex Mono</div>
          <p style="font-family: var(--lib-font-mono); margin: 4px 0;">ISBN 978-0-06-088328-7</p>
          <p style="font-family: var(--lib-font-mono); margin: 4px 0;">Due 2026-07-24 · 12 days overdue</p>
        </div>
      </div>
    `,
  }),
};
