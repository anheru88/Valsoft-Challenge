import { Component, inject, signal } from '@angular/core';
import { AbstractControl, FormBuilder, ReactiveFormsModule, ValidationErrors, Validators } from '@angular/forms';
import { DatePipe } from '@angular/common';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar } from '@angular/material/snack-bar';
import { apiErrorCode, fieldErrors } from '../../core/api/api-error';
import { businessMessage } from '../../core/api/error-message';
import { AuthStore } from '../../core/auth.store';
import { PageHeader } from '../../shared/ui/page-header';
import { RoleBadge } from '../../shared/ui/role-badge';
import { InlineAlert } from '../../shared/ui/inline-alert';
import { AuthApiService, PasswordChange } from '../auth/data/auth-api.service';

/** New password and its confirmation must agree — checked on the group so the
 *  error attaches once, not to whichever field was edited last. */
function passwordsMatch(group: AbstractControl): ValidationErrors | null {
  const password = group.get('password')?.value;
  const confirmation = group.get('password_confirmation')?.value;

  return password && confirmation && password !== confirmation ? { mismatch: true } : null;
}

@Component({
  selector: 'lib-profile-page',
  standalone: true,
  imports: [ReactiveFormsModule, DatePipe, MatFormFieldModule, MatInputModule,
            MatButtonModule, MatIconModule, PageHeader, RoleBadge, InlineAlert],
  templateUrl: './profile-page.html',
  styles: [`
    .narrow { max-width: 760px; }
    .cards { display: grid; gap: var(--sp-5); }
    dl.facts { margin: 0; display: grid; grid-template-columns: max-content 1fr; gap: var(--sp-3) var(--sp-5); }
    dl.facts dt { color: var(--lib-ink-soft); }
    dl.facts dd { margin: 0; }
    h3 { margin: 0 0 var(--sp-4); }
    lib-inline-alert { display: block; margin-bottom: var(--sp-4); }
    .pw-form { display: grid; gap: var(--sp-2); }
    .full { width: 100%; }
    .form-actions { justify-content: flex-start; margin-top: var(--sp-2); }
  `],
})
export class ProfilePage {
  private readonly fb = inject(FormBuilder);
  private readonly auth = inject(AuthStore);
  private readonly api = inject(AuthApiService);
  private readonly snack = inject(MatSnackBar);

  readonly user = this.auth.user;
  readonly role = this.auth.role;

  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    current_password: ['', Validators.required],
    // Mirrors FR-AUTH-5: at least eight characters with a letter and a number.
    // The server is the authority; this only spares a round-trip.
    password: ['', [Validators.required, Validators.minLength(8), Validators.pattern(/(?=.*[A-Za-z])(?=.*\d)/)]],
    password_confirmation: ['', Validators.required],
  }, { validators: passwordsMatch });

  submit(): void {
    this.serverError.set(null);
    this.fieldErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);

    this.api.changePassword(this.form.getRawValue() as PasswordChange).subscribe({
      next: () => {
        this.saving.set(false);
        this.form.reset();
        this.snack.open('Your password has been changed.', undefined, { duration: 4000 });
      },
      error: (err: unknown) => {
        this.saving.set(false);
        // A wrong current password is a business refusal, not a malformed
        // field: name it against the field the person can fix.
        if (apiErrorCode(err) === 'CURRENT_PASSWORD_INVALID') {
          this.form.controls.current_password.setErrors({ wrong: true });
          return;
        }
        this.fieldErrors.set(fieldErrors(err));
        this.serverError.set(businessMessage(err, 'Could not change your password.'));
      },
    });
  }
}
