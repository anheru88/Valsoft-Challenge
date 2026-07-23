import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { apiErrorCode } from '../../../core/api/api-error';
import { AuthStore } from '../../../core/auth.store';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { AuthApiService } from '../data/auth-api.service';
import { DemoAccount, DemoAccounts } from '../demo-accounts';

@Component({
  selector: 'lib-login-page',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, MatFormFieldModule, MatInputModule,
            MatButtonModule, MatIconModule, MatProgressSpinnerModule, InlineAlert, DemoAccounts],
  templateUrl: './login-page.html',
  styles: [`
    h2 { margin: 0 0 var(--sp-4); }
    .full { width: 100%; }
    form { display: grid; gap: var(--sp-2); margin-top: var(--sp-3); }
    .alt { text-align: center; margin: var(--sp-4) 0 0; }
    .btn-spinner { display: inline-block; margin-right: var(--sp-2); }
    lib-inline-alert { display: block; margin-bottom: var(--sp-3); }
  `],
})
export class LoginPage {
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  private readonly auth = inject(AuthStore);
  private readonly api = inject(AuthApiService);

  readonly loading = signal(false);
  readonly showPassword = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly sessionExpired = computed(() => this.route.snapshot.queryParamMap.get('reason') === 'expired');

  readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  /**
   * Fills the form with a seeded account. It stops short of submitting: the
   * reviewer sees the credentials that were used and presses the button
   * themselves.
   */
  fillWith(account: DemoAccount): void {
    this.errorMessage.set(null);
    this.form.setValue({ email: account.email, password: account.password });
  }

  submit(): void {
    this.errorMessage.set(null);
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading.set(true);

    this.api.login(this.form.getRawValue()).subscribe({
      next: ({ data }) => {
        this.auth.startSession(data.token, data.user);

        // Back to wherever the guard interrupted, or to the screen this account
        // is actually equipped to use.
        const redirect = this.route.snapshot.queryParamMap.get('redirect');
        this.router.navigateByUrl(redirect ?? (this.auth.can('dashboard.view') ? '/dashboard' : '/books'));
      },
      error: (error: unknown) => {
        this.loading.set(false);
        this.errorMessage.set(this.messageFor(error));
      },
    });
  }

  /**
   * A refusal to sign in is a business answer, not a system failure, so it is
   * shown next to the form rather than in a toast (PRD 5).
   */
  private messageFor(error: unknown): string {
    switch (apiErrorCode(error)) {
      case 'INVALID_CREDENTIALS':
      case 'VALIDATION_FAILED':
        return 'El correo o la contraseña no son correctos.';
      case 'USER_INACTIVE':
        return 'Tu cuenta está desactivada. Contacta con la biblioteca.';
      case 'RATE_LIMITED':
        return 'Demasiados intentos. Espera un minuto y vuelve a probar.';
      default:
        return 'No se pudo iniciar sesión. Inténtalo de nuevo.';
    }
  }
}
