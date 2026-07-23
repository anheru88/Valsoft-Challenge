import { Component, inject, signal } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { InlineAlert } from '../../../shared/ui/inline-alert';

function passwordsMatch(group: AbstractControl) {
  return group.get('password')?.value === group.get('password_confirmation')?.value ? null : { mismatch: true };
}

@Component({
  selector: 'lib-register-page',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, MatFormFieldModule, MatInputModule, MatButtonModule, InlineAlert],
  templateUrl: './register-page.html',
  styles: [`
    h2 { margin: 0 0 var(--sp-1); } .intro { margin: 0 0 var(--sp-4); }
    .full { width: 100%; } form { display: grid; gap: var(--sp-2); }
    .alt { text-align: center; margin: var(--sp-4) 0 0; }
    lib-inline-alert { display: block; margin-bottom: var(--sp-3); }
  `],
})
export class RegisterPage {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);

  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  /** Errores 422 del API mapeados por campo (details.errors) */
  readonly serverErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', Validators.required],
  }, { validators: passwordsMatch });

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading.set(true);
    // TODO API: POST /api/v1/auth/register → auto-login y redirigir a /books
    // En error 422: this.serverErrors.set(mapFieldErrors(e)); p. ej. email ya registrado.
    this.loading.set(false);
  }
}
