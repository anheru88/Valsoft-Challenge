import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'lib-not-found-page',
  standalone: true,
  imports: [RouterLink, MatButtonModule],
  template: `
    <div class="err">
      <span class="code mono">404</span>
      <h1>This page is not in the catalogue</h1>
      <p class="muted">The address does not exist, or the record was withdrawn. Try again from the start.</p>
      <a mat-flat-button color="primary" routerLink="/">Back to the start</a>
    </div>
  `,
  styleUrl: './error-pages.scss',
})
export class NotFoundPage {}
