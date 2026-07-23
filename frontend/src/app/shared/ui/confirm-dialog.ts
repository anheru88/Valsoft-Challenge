import { Component, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';

export interface ConfirmDialogData {
  title: string;          // e.g. 'Delete «Dune»?'
  message: string;        // the consequence, in plain language
  confirmLabel?: string;  // e.g. 'Delete book'
  destructive?: boolean;
}

/** Confirmation dialog for destructive actions.
 *  Usage: dialog.open(ConfirmDialog, { data: {...} }).afterClosed() → boolean */
@Component({
  selector: 'lib-confirm-dialog',
  standalone: true,
  imports: [MatDialogModule, MatButtonModule],
  template: `
    <h2 mat-dialog-title>{{ data.title }}</h2>
    <mat-dialog-content><p>{{ data.message }}</p></mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="ref.close(false)">Cancel</button>
      <button mat-flat-button [color]="data.destructive ? 'warn' : 'primary'"
              cdkFocusInitial (click)="ref.close(true)">
        {{ data.confirmLabel ?? 'Confirm' }}
      </button>
    </mat-dialog-actions>
  `,
})
export class ConfirmDialog {
  readonly ref = inject(MatDialogRef<ConfirmDialog>);
  readonly data = inject<ConfirmDialogData>(MAT_DIALOG_DATA);
}
