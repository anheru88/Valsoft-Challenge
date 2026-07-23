import type { Meta, StoryObj } from '@storybook/angular';
import { AvailabilityBadge } from '../availability-badge';

const meta: Meta<AvailabilityBadge> = {
  title: 'Shared UI/AvailabilityBadge',
  component: AvailabilityBadge,
  tags: ['autodocs'],
};
export default meta;
type S = StoryObj<AvailabilityBadge>;

export const Available: S =    { args: { available: 3, total: 5 } };
export const LastCopy: S =   { args: { available: 1, total: 4 } };
export const NoCopiesLeft: S = { args: { available: 0, total: 5 } };
