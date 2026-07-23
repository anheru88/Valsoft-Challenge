import type { Meta, StoryObj } from '@storybook/angular';
import { moduleMetadata } from '@storybook/angular';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { ConfirmDialog } from '../confirm-dialog';

/** El contenido del diálogo renderizado plano (sin overlay) para revisión visual. */
const meta: Meta<ConfirmDialog> = {
  title: 'Design System/ConfirmDialog',
  component: ConfirmDialog,
  tags: ['autodocs'],
  decorators: [
    moduleMetadata({
      providers: [
        { provide: MatDialogRef, useValue: { close: () => {} } },
        { provide: MAT_DIALOG_DATA, useValue: {
            title: '¿Eliminar «Cien años de soledad»?',
            message: 'El libro se retirará del catálogo. Los préstamos ya devueltos conservan su historial.',
            confirmLabel: 'Eliminar libro',
            destructive: true,
        } },
      ],
    }),
  ],
};
export default meta;
export const Destructivo: StoryObj<ConfirmDialog> = {};
