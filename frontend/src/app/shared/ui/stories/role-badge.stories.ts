import type { Meta, StoryObj } from '@storybook/angular';
import { RoleBadge } from '../role-badge';

const meta: Meta<RoleBadge> = { title: 'Design System/RoleBadge', component: RoleBadge, tags: ['autodocs'] };
export default meta;
type S = StoryObj<RoleBadge>;

export const Admin: S =         { args: { role: 'admin' } };
export const Bibliotecario: S = { args: { role: 'librarian' } };
export const Socio: S =         { args: { role: 'member' } };
