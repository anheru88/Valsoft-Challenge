import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { Author } from '../../core/models';

/** Alta/edición de autor en diálogo (formulario pequeño). */
@Component({
  selector: 'lib-author-form-dialog',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  template: `
    <h2 mat-dialog-title>{{ data ? 'Editar autor' : 'Nuevo autor' }}</h2>
    <mat-dialog-content>
      <form [formGroup]="form" id="author-form" (ngSubmit)="save()" class="grid">
        <mat-form-field appearance="outline">
          <mat-label>Nombre</mat-label>
          <input matInput formControlName="name" maxlength="255" cdkFocusInitial />
          @if (form.controls.name.hasError('required')) { <mat-error>El nombre es obligatorio.</mat-error> }
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Año de nacimiento</mat-label>
          <input matInput type="number" formControlName="birth_year" />
        </mat-form-field>
        <mat-form-field appearance="outline">
          <mat-label>Biografía</mat-label>
          <textarea matInput formControlName="bio" rows="3" maxlength="2000"></textarea>
        </mat-form-field>
      </form>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancelar</button>
      <button mat-flat-button color="primary" type="submit" form="author-form" [disabled]="saving()">
        {{ data ? 'Guardar cambios' : 'Crear autor' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`.grid { display: grid; gap: var(--sp-2); min-width: min(420px, 80vw); padding-top: var(--sp-2); }`],
})
export class AuthorFormDialog {
  readonly ref = inject(MatDialogRef<AuthorFormDialog>);
  readonly data = inject<Author | null>(MAT_DIALOG_DATA);
  private readonly fb = inject(FormBuilder);

  readonly saving = signal(false);
  readonly form = this.fb.nonNullable.group({
    name: [this.data?.name ?? '', Validators.required],
    birth_year: [this.data?.birth_year ?? null as number | null],
    bio: [this.data?.bio ?? ''],
  });

  save(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    // TODO API: POST /authors | PUT /authors/{id}
    this.ref.close(true);
  }
}
