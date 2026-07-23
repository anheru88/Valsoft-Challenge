import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { Category } from '../../core/models';

@Component({
  selector: 'lib-category-form-dialog',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  template: `
    <h2 mat-dialog-title>{{ data ? 'Edit category' : 'New category' }}</h2>
    <mat-dialog-content>
      <form [formGroup]="form" id="cat-form" (ngSubmit)="save()" class="grid">
        <mat-form-field appearance="outline">
          <mat-label>Name</mat-label>
          <input matInput formControlName="name" maxlength="100" cdkFocusInitial />
          <mat-hint>The slug is generated automatically.</mat-hint>
          @if (form.controls.name.hasError('required')) { <mat-error>El nombre es obligatorio.</mat-error> }
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Description</mat-label>
          <textarea matInput formControlName="description" rows="3" maxlength="500"></textarea>
        </mat-form-field>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancel</button>
      <button mat-flat-button color="primary" type="submit" form="cat-form">
        {{ data ? 'Save changes' : 'Create category' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`.grid { display: grid; gap: var(--sp-2); min-width: min(380px, 80vw); padding-top: var(--sp-2); }`],
})
export class CategoryFormDialog {
  readonly ref = inject(MatDialogRef<CategoryFormDialog>);
  readonly data = inject<Category | null>(MAT_DIALOG_DATA);
  private readonly fb = inject(FormBuilder);

  readonly form = this.fb.nonNullable.group({
    name: [this.data?.name ?? '', Validators.required],
    description: [this.data?.description ?? ''],
  });

  save(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    // TODO API: POST /categories | PUT /categories/{id}; 422 nombre duplicado → error en campo.
    this.ref.close(true);
  }
}
