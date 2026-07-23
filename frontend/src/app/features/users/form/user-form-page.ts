import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatSnackBar } from '@angular/material/snack-bar';
import { fieldErrors } from '../../../core/api/api-error';
import { businessMessage } from '../../../core/api/error-message';
import { AuthStore } from '../../../core/auth.store';
import { Role, User } from '../../../core/models';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';
import { UserPayload, UsersApiService } from '../data/users-api.service';

@Component({
  selector: 'lib-user-form-page',
  standalone: true,
  imports: [ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatSelectModule,
            MatButtonModule, PageHeader, InlineAlert],
  templateUrl: './user-form-page.html',
  styles: [`.narrow { max-width: 760px; } lib-inline-alert { display: block; margin-bottom: var(--sp-4); }`],
})
export class UserFormPage {
  private readonly fb = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly auth = inject(AuthStore);
  private readonly snack = inject(MatSnackBar);
  private readonly api = inject(UsersApiService);

  private readonly userId = Number(this.route.snapshot.paramMap.get('id')) || null;

  readonly isAdmin = this.auth.isAdmin;
  readonly isEdit = computed(() => this.userId !== null);
  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    // A librarian may only create members, and the server enforces it; the
    // select is simply not offered to them.
    role: ['member' as Role],
    password: ['', [Validators.minLength(8)]],
  });

  constructor() {
    if (this.userId) {
      this.api.get(this.userId).subscribe({
        next: ({ data }) => this.fill(data),
        error: (err: unknown) => this.serverError.set(businessMessage(err, 'Could not load this account.')),
      });
    }
  }

  private fill(user: User): void {
    this.form.patchValue({
      name: user.name,
      email: user.email,
      role: user.roles[0] ?? 'member',
    });
  }

  submit(): void {
    this.serverError.set(null);
    this.fieldErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);

    const value = this.form.getRawValue();
    const payload: UserPayload = {
      name: value.name,
      email: value.email,
      // A create always names the role — a librarian's form is fixed to
      // `member`, which is the only one they may assign. On an edit the field
      // is sent only by an administrator, so a self-service profile change
      // never reaches the role branch of the server's authorization.
      ...(this.isEdit() ? (this.isAdmin() ? { role: value.role } : {}) : { role: value.role, password: value.password }),
    };

    const request = this.userId
      ? this.api.update(this.userId, payload)
      : this.api.create(payload);

    request.subscribe({
      next: () => {
        this.saving.set(false);
        this.snack.open(this.isEdit() ? 'Changes saved' : 'User created', undefined, { duration: 4000 });
        this.router.navigate(['/users']);
      },
      error: (err: unknown) => {
        this.saving.set(false);
        // A duplicate email is a 422 under the email field; LAST_ADMIN_PROTECTED
        // is a 409 about the library as a whole and is said at the top.
        const perField = fieldErrors(err);
        this.fieldErrors.set(perField);

        if (Object.keys(perField).length === 0) {
          this.serverError.set(businessMessage(err, 'Could not save the account.'));
        }
      },
    });
  }

  cancel(): void { this.router.navigate(['/users']); }
}
