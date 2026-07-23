import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { AuthStore } from '../../../core/auth.store';
import { InlineAlert } from '../../../shared/ui/inline-alert';

@Component({
  selector: 'lib-login-page',
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, MatFormFieldModule, MatInputModule,
            MatButtonModule, MatIconModule, MatProgressSpinnerModule, InlineAlert],
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

  readonly loading = signal(false);
  readonly showPassword = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly sessionExpired = computed(() => this.route.snapshot.queryParamMap.get('reason') === 'expired');

  readonly form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  submit(): void {
    this.errorMessage.set(null);
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading.set(true);

    // TODO API: POST /api/v1/auth/login → { token, user }
    // AuthApiService.login(this.form.getRawValue()).subscribe({
    //   next: ({ token, user }) => {
    //     this.auth.startSession(token, user);
    //     const redirect = this.route.snapshot.queryParamMap.get('redirect');
    //     this.router.navigateByUrl(redirect ?? (user.role === 'member' ? '/books' : '/dashboard'));
    //   },
    //   error: (e) => {
    //     this.loading.set(false);
    //     this.errorMessage.set(e.status === 422 || e.status === 401
    //       ? 'El correo o la contraseña no son correctos.'
    //       : e.status === 403 ? 'Tu cuenta está desactivada. Contacta con la biblioteca.'
    //       : 'No se pudo iniciar sesión. Inténtalo de nuevo.');
    //   },
    // });
    this.loading.set(false); // quitar al conectar el API
  }
}
