import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { fieldErrors } from '../../core/api/api-error';
import { businessMessage } from '../../core/api/error-message';
import { Category } from '../../core/models';
import { InlineAlert } from '../../shared/ui/inline-alert';
import { CategoriesApiService } from './data/categories-api.service';

@Component({
  selector: 'lib-category-form-dialog',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule, InlineAlert],
  template: `
    <h2 mat-dialog-title>{{ data ? 'Edit category' : 'New category' }}</h2>
    <mat-dialog-content>
      @if (serverError()) { <lib-inline-alert tone="danger">{{ serverError() }}</lib-inline-alert> }
      <form [formGroup]="form" id="cat-form" (ngSubmit)="save()" class="grid">
        <mat-form-field appearance="outline">
          <mat-label>Name</mat-label>
          <input matInput formControlName="name" maxlength="100" cdkFocusInitial />
          <mat-hint>The slug is generated automatically.</mat-hint>
          @if (form.controls.name.hasError('required')) { <mat-error>The name is required.</mat-error> }
          @if (fieldErrors()['name']) { <mat-error>{{ fieldErrors()['name'] }}</mat-error> }
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Description</mat-label>
          <textarea matInput formControlName="description" rows="3" maxlength="500"></textarea>
        </mat-form-field>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancel</button>
      <button mat-flat-button color="primary" type="submit" form="cat-form" [disabled]="saving()">
        {{ data ? 'Save changes' : 'Create category' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .grid { display: grid; gap: var(--sp-2); min-width: min(380px, 80vw); padding-top: var(--sp-2); }
    lib-inline-alert { display: block; margin-bottom: var(--sp-3); }
  `],
})
export class CategoryFormDialog {
  readonly ref = inject(MatDialogRef<CategoryFormDialog>);
  readonly data = inject<Category | null>(MAT_DIALOG_DATA);
  private readonly fb = inject(FormBuilder);
  private readonly api = inject(CategoriesApiService);

  readonly saving = signal(false);
  readonly serverError = signal<string | null>(null);
  readonly fieldErrors = signal<Record<string, string>>({});

  readonly form = this.fb.nonNullable.group({
    name: [this.data?.name ?? '', Validators.required],
    description: [this.data?.description ?? ''],
  });

  save(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.saving.set(true);
    this.serverError.set(null);
    this.fieldErrors.set({});

    const value = this.form.getRawValue();
    const payload = { name: value.name, description: value.description || null };
    const request = this.data ? this.api.update(this.data.id, payload) : this.api.create(payload);

    request.subscribe({
      next: () => this.ref.close(true),
      error: (err: unknown) => {
        this.saving.set(false);
        // A duplicate name is a 422 about the name field, and belongs under it.
        const perField = fieldErrors(err);
        this.fieldErrors.set(perField);

        if (Object.keys(perField).length === 0) {
          this.serverError.set(businessMessage(err, 'Could not save the category.'));
        }
      },
    });
  }
}
