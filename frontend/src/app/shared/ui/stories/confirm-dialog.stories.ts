import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata } from '@storybook/angular';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { ConfirmDialog } from '../confirm-dialog';

/** The dialog content rendered flat, without the overlay, for visual review. */
const meta: Meta<ConfirmDialog> = {
  title: 'Shared UI/ConfirmDialog',
  component: ConfirmDialog,
  tags: ['autodocs'],
  decorators: [
    moduleMetadata({
      providers: [
        { provide: MatDialogRef, useValue: { close: () => {} } },
        { provide: MAT_DIALOG_DATA, useValue: {
            title: 'Delete «One Hundred Years of Solitude»?',
            message: 'The book will be withdrawn from the catalogue. Returned loans keep their history.',
            confirmLabel: 'Delete book',
            destructive: true,
        } },
      ],
    }),
  ],
};
export default meta;
export const Destructive: StoryObj<ConfirmDialog> = {};
