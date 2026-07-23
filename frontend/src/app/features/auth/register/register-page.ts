import { Component, inject, signal } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { fieldErrors } from '../../../core/api/api-error';
import { businessMessage } from '../../../core/api/error-message';
import { AuthStore } from '../../../core/auth.store';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { AuthApiService } from '../data/auth-api.service';

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
  private readonly auth = inject(AuthStore);
  private readonly api = inject(AuthApiService);

  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  /** 422 messages from the API, keyed by field (`details.errors`). */
  readonly serverErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', Validators.required],
  }, { validators: passwordsMatch });

  /** Registering creates a member account and signs it in (FR-AUTH-2). */
  submit(): void {
    this.errorMessage.set(null);
    this.serverErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading.set(true);

    this.api.register(this.form.getRawValue()).subscribe({
      next: ({ data }) => {
        this.auth.startSession(data.token, data.user);
        this.router.navigateByUrl('/books');
      },
      error: (err: unknown) => {
        this.loading.set(false);
        // A taken email is a 422 about that field; anything else is said once
        // above the form.
        const perField = fieldErrors(err);
        this.serverErrors.set(perField);

        if (Object.keys(perField).length === 0) {
          this.errorMessage.set(businessMessage(err, 'Could not create the account. Please try again.'));
        }
      },
    });
  }
}
