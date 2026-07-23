import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'lib-forbidden-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule],
  template: `
    <div class="err">
      <span class="code mono">403</span>
      <h1>That section is not on your library card</h1>
      <p class="muted">Your account has no permission to view this page. If you think it should, talk to administration.</p>
      <a mat-flat-button color="primary" routerLink="/books">Go to the catalogue</a>
    </div>
  `,
  styleUrl: './error-pages.scss',
})
export class ForbiddenPage {}
