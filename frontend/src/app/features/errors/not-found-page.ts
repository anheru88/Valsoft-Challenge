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
      <h1>Página fuera de catálogo</h1>
      <p class="muted">La dirección no existe o el recurso fue retirado. Prueba desde el inicio.</p>
      <a mat-flat-button color="primary" routerLink="/">Volver al inicio</a>
    </div>
  `,
  styleUrl: './error-pages.scss',
})
export class NotFoundPage {}
