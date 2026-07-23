import type { Meta, StoryObj } from '@storybook/angular';
import { BookCover } from '../book-cover';

/** Inlined rather than fetched: a story that needs the network is a story that
 *  fails in CI for reasons that have nothing to do with the component. */
const COVER = 'data:image/svg+xml;utf8,' + encodeURIComponent(`
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300">
    <rect width="200" height="300" fill="#1f3b2c"/>
    <rect x="14" y="14" width="172" height="272" fill="none" stroke="#c8b47a" stroke-width="2"/>
    <text x="100" y="140" fill="#f2ead6" font-family="Georgia, serif" font-size="19"
          text-anchor="middle">One Hundred</text>
    <text x="100" y="166" fill="#f2ead6" font-family="Georgia, serif" font-size="19"
          text-anchor="middle">Years of</text>
    <text x="100" y="192" fill="#f2ead6" font-family="Georgia, serif" font-size="19"
          text-anchor="middle">Solitude</text>
  </svg>`);

const meta: Meta<BookCover> = {
  title: 'Shared UI/BookCover',
  component: BookCover,
  tags: ['autodocs'],
  render: (args) => ({
    props: args,
    template: `<div style="width: 120px"><lib-book-cover [url]="url" [title]="title" /></div>`,
  }),
};
export default meta;
type S = StoryObj<BookCover>;

export const WithCover: S = { args: { url: COVER, title: 'One Hundred Years of Solitude' } };

/** The common case: most of the catalogue has no cover on file. */
export const Fallback: S = { args: { url: null, title: 'The Aleph' } };

export const InAListRow: S = {
  args: { url: null, title: 'The Aleph' },
  render: (args) => ({
    props: args,
    template: `
      <div style="display: flex; gap: 12px; align-items: center">
        <div style="width: 40px"><lib-book-cover [url]="url" [title]="title" /></div>
        <div>
          <div style="font-weight: 600">The Aleph</div>
          <div style="color: var(--lib-ink-faint); font-size: 13px">Jorge Luis Borges</div>
        </div>
      </div>`,
  }),
};
