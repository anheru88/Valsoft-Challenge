import { Component, computed, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AuthStore } from '../../../core/auth.store';
import { PageHeader } from '../../../shared/ui/page-header';
import { InlineAlert } from '../../../shared/ui/inline-alert';

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

  readonly isAdmin = this.auth.isAdmin;
  readonly isEdit = computed(() => this.route.snapshot.paramMap.has('id'));
  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    role: ['member'],                       // los bibliotecarios solo crean socios (el API lo fuerza)
    password: ['', [Validators.minLength(8)]],
  });

  submit(): void {
    this.serverError.set(null); this.fieldErrors.set({});
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    // TODO API: POST /users | PUT /users/{id}
    //  422 email duplicado → fieldErrors['email']
    //  409 LAST_ADMIN_PROTECTED (demoting the only admin) → serverError with the message.
    this.saving.set(false);
    this.snack.open(this.isEdit() ? 'Cambios guardados' : 'Usuario creado', undefined, { duration: 4000 });
    this.router.navigate(['/users']);
  }

  cancel(): void { this.router.navigate(['/users']); }
}
