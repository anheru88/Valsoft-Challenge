import { Component, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';

export interface DemoAccount {
  role: string;
  email: string;
  password: string;
  sees: string;
}

/**
 * The seeded accounts, offered under the sign-in form.
 *
 * This build is an assessment: a reviewer should not have to hunt through a
 * README for credentials. Each row fills the form rather than signing in
 * outright, so the reviewer still sees what was typed and stays in control of
 * the submit.
 *
 * It is deliberately one component, easy to delete the day this stops being a
 * demo.
 */
@Component({
  selector: 'lib-demo-accounts',
  standalone: true,
  imports: [MatButtonModule],
  template: `
    <section class="demo" aria-labelledby="demo-heading">
      <h3 id="demo-heading">Sign in and look around</h3>
      <p class="hint">
        Every account uses the password <code>{{ password }}</code>.
        Pick one to fill the form.
      </p>

      <ul>
        @for (account of accounts; track account.email) {
          <li>
            <div class="who">
              <strong>{{ account.role }}</strong>
              <span class="email">{{ account.email }}</span>
              <span class="sees">{{ account.sees }}</span>
            </div>
            <button mat-stroked-button type="button"
                    (click)="use.emit(account)"
                    [attr.aria-label]="'Fill the form with the ' + account.role + ' account'">
              Use
            </button>
          </li>
        }
      </ul>
    </section>
  `,
  styles: [`
    .demo {
      margin-top: var(--sp-6);
      padding: var(--sp-4) var(--sp-5) var(--sp-5);
      background: var(--lib-brass-soft);
      border: 1px solid var(--lib-line);
      border-radius: var(--lib-radius);
    }
    h3 { font-family: var(--lib-font-display); font-size: 16px; margin: 0 0 var(--sp-1); }
    .hint { font-size: 13px; color: var(--lib-ink-soft); margin: 0 0 var(--sp-3); }
    code {
      font-family: var(--lib-font-mono); font-size: 12px;
      background: var(--lib-surface); padding: 1px 5px; border-radius: var(--lib-radius-sm);
    }
    ul { list-style: none; margin: 0; padding: 0; display: grid; gap: var(--sp-2); }
    li {
      display: flex; align-items: center; gap: var(--sp-3);
      padding: var(--sp-3); background: var(--lib-surface);
      border: 1px solid var(--lib-line); border-radius: var(--lib-radius-sm);
    }
    .who { display: grid; gap: 1px; min-width: 0; flex: 1; }
    strong { font-size: 14px; font-weight: 600; }
    .email { font-family: var(--lib-font-mono); font-size: 12px; color: var(--lib-ink-soft); overflow-wrap: anywhere; }
    .sees { font-size: 12px; color: var(--lib-ink-faint); }
    button { flex: none; }
  `],
})
export class DemoAccounts {
  /** Emitted when a reviewer picks an account to try. */
  readonly use = output<DemoAccount>();

  readonly password = 'password';

  readonly accounts: DemoAccount[] = [
    {
      role: 'Administrator',
      email: 'admin@librarium.test',
      password: this.password,
      sees: 'Everything, including user management and the reports',
    },
    {
      role: 'Librarian',
      email: 'librarian@librarium.test',
      password: this.password,
      sees: 'The desk: catalogue, circulation and the dashboard',
    },
    {
      role: 'Member',
      email: 'member@librarium.test',
      password: this.password,
      sees: 'The catalogue and their own loans',
    },
  ];
}
