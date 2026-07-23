import { Component, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';

export interface ConfirmDialogData {
  title: string;          // p. ej. '¿Eliminar «Dune»?'
  message: string;        // consecuencia en lenguaje llano
  confirmLabel?: string;  // p. ej. 'Eliminar libro'
  destructive?: boolean;
}

/** Diálogo de confirmación para acciones destructivas.
 *  Uso: dialog.open(ConfirmDialog, { data: {...} }).afterClosed() → boolean */
@Component({
  selector: 'lib-confirm-dialog',
  standalone: true,
  imports: [MatDialogModule, MatButtonModule],
  template: `
    <h2 mat-dialog-title>{{ data.title }}</h2>
    <mat-dialog-content><p>{{ data.message }}</p></mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancelar</button>
      <button mat-flat-button [color]="data.destructive ? 'warn' : 'primary'"
              cdkFocusInitial (click)="ref.close(true)">
        {{ data.confirmLabel ?? 'Confirmar' }}
      </button>
    </mat-dialog-actions>
  `,
})
export class ConfirmDialog {
  readonly ref = inject(MatDialogRef<ConfirmDialog>);
  readonly data = inject<ConfirmDialogData>(MAT_DIALOG_DATA);
}
