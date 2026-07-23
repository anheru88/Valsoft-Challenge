import { inject } from '@angular/core';
import { CanDeactivateFn } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { map } from 'rxjs';
import { ConfirmDialog } from '../../shared/ui/confirm-dialog';

/**
 * A page with a form the person could be part-way through implements this, so
 * the guard can ask it — the guard knows nothing about any particular form.
 */
export interface HasUnsavedChanges {
  hasUnsavedChanges(): boolean;
}

/**
 * Confirms before leaving a form with edits that were never saved (PRD 8.4).
 *
 * A save or an explicit cancel marks its form pristine first, so this only
 * fires on the accidental exits — a stray nav click, the back button — where
 * the warning is worth the friction.
 */
export const pendingChangesGuard: CanDeactivateFn<HasUnsavedChanges> = (component) => {
  if (!component.hasUnsavedChanges()) {
    return true;
  }

  return inject(MatDialog)
    .open(ConfirmDialog, {
      data: {
        title: 'Leave without saving?',
        message: 'You have changes on this page that have not been saved. Leaving now discards them.',
        confirmLabel: 'Leave',
        destructive: true,
      },
    })
    .afterClosed()
    .pipe(map(Boolean));
};
