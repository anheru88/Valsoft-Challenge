import type { Meta, StoryObj } from '@storybook/angular';
import { Observable, of, timer } from 'rxjs';
import { map } from 'rxjs';
import { EntityAutocomplete } from '../entity-autocomplete';

interface Member { id: number; name: string; email: string; }

const MEMBERS: Member[] = [
  { id: 1, name: 'Ada Lovelace', email: 'ada@librarium.test' },
  { id: 2, name: 'Alan Turing', email: 'alan@librarium.test' },
  { id: 3, name: 'Grace Hopper', email: 'grace@librarium.test' },
];

/** A fake server: filters the fixture and answers after a short delay, so the
 *  spinner and the debounce are visible without a real backend. */
function fakeSearch(term: string): Observable<Member[]> {
  const needle = term.toLowerCase();
  return timer(400).pipe(
    map(() => MEMBERS.filter(m => m.name.toLowerCase().includes(needle) || m.email.includes(needle))),
  );
}

const meta: Meta<EntityAutocomplete<Member>> = {
  title: 'Shared UI/EntityAutocomplete',
  component: EntityAutocomplete,
  tags: ['autodocs'],
  argTypes: { selected: { action: 'selected' } },
  render: (args) => ({
    props: args,
    template: `
      <div style="max-width: 420px">
        <lib-entity-autocomplete
          [label]="label" [placeholder]="placeholder" [hint]="hint" [icon]="icon"
          [emptyLabel]="emptyLabel" [search]="search"
          [primaryText]="primaryText" [secondaryText]="secondaryText"
          [disabledWhen]="disabledWhen"
          (selected)="selected($event)" />
      </div>`,
  }),
};
export default meta;
type S = StoryObj<EntityAutocomplete<Member>>;

const base = {
  label: 'Member',
  placeholder: 'Type a name or email…',
  hint: 'Search by name or email',
  icon: 'person_search',
  emptyLabel: 'No member matches',
  search: fakeSearch,
  primaryText: (m: Member) => m.name,
  secondaryText: (m: Member) => m.email,
  disabledWhen: () => false,
};

export const Members: S = { args: base };

/** Some hits exist but cannot be picked — a book with no free copy, say. */
export const WithDisabledOptions: S = {
  args: { ...base, disabledWhen: (m: Member) => m.id === 2 },
};

/** Server that never matches, to show the "nothing matches" line. */
export const NoMatches: S = { args: { ...base, search: () => of([] as Member[]) } };
