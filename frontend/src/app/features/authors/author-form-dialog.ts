import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { fieldErrors } from '../../core/api/api-error';
import { businessMessage } from '../../core/api/error-message';
import { Author } from '../../core/models';
import { InlineAlert } from '../../shared/ui/inline-alert';
import { AuthorsApiService } from './data/authors-api.service';

/** Creating and editing an author in a dialog: a small form. */
@Component({
  selector: 'lib-author-form-dialog',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule, InlineAlert],
  template: `
    <h2 mat-dialog-title>{{ data ? 'Edit author' : 'New author' }}</h2>
    <mat-dialog-content>
      @if (serverError()) { <lib-inline-alert tone="danger">{{ serverError() }}</lib-inline-alert> }
      <form [formGroup]="form" id="author-form" (ngSubmit)="save()" class="grid">
        <mat-form-field appearance="outline">
          <mat-label>Name</mat-label>
          <input matInput formControlName="name" maxlength="255" cdkFocusInitial />
          @if (form.controls.name.hasError('required')) { <mat-error>The name is required.</mat-error> }
          @if (fieldErrors()['name']) { <mat-error>{{ fieldErrors()['name'] }}</mat-error> }
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Year of birth</mat-label>
          <input matInput type="number" formControlName="birth_year" />
          @if (fieldErrors()['birth_year']) { <mat-error>{{ fieldErrors()['birth_year'] }}</mat-error> }
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Biography</mat-label>
          <textarea matInput formControlName="bio" rows="3" maxlength="2000"></textarea>
        </mat-form-field>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancel</button>
      <button mat-flat-button color="primary" type="submit" form="author-form" [disabled]="saving()">
        {{ data ? 'Save changes' : 'Create author' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .grid { display: grid; gap: var(--sp-2); min-width: min(420px, 80vw); padding-top: var(--sp-2); }
    lib-inline-alert { display: block; margin-bottom: var(--sp-3); }
  `],
})
export class AuthorFormDialog {
  readonly ref = inject(MatDialogRef<AuthorFormDialog>);
  readonly data = inject<Author | null>(MAT_DIALOG_DATA);
  private readonly fb = inject(FormBuilder);
  private readonly api = inject(AuthorsApiService);

  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: [this.data?.name ?? '', Validators.required],
    birth_year: [this.data?.birth_year ?? null as number | null],
    bio: [this.data?.bio ?? ''],
  });

  save(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.saving.set(true);
    this.serverError.set(null);
    this.fieldErrors.set({});

    const value = this.form.getRawValue();
    const payload = { name: value.name, birth_year: value.birth_year, bio: value.bio || null };
    const request = this.data ? this.api.update(this.data.id, payload) : this.api.create(payload);

    request.subscribe({
      next: () => this.ref.close(true),
      error: (err: unknown) => {
        this.saving.set(false);
        // The dialog stays open on a rejection: closing it would throw away
        // what was typed along with the reason it was refused.
        const perField = fieldErrors(err);
        this.fieldErrors.set(perField);

        if (Object.keys(perField).length === 0) {
          this.serverError.set(businessMessage(err, 'Could not save the author.'));
        }
      },
    });
  }
}
