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
      <h1>Esta sección no está en tu carné</h1>
      <p class="muted">Tu cuenta no tiene permisos para ver esta página. Si crees que deberías tenerlos, habla con la administración.</p>
      <a mat-flat-button color="primary" routerLink="/books">Ir al catálogo</a>
    </div>
  `,
  styleUrl: './error-pages.scss',
})
export class ForbiddenPage {}
