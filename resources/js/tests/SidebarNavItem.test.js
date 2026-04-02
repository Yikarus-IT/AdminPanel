import { fireEvent, render, screen } from '@testing-library/vue';
import { describe, expect, it } from 'vitest';
import SidebarNavItem from '../components/SidebarNavItem.vue';

describe('SidebarNavItem', () => {
    it('emits the selected label when clicked', async () => {
        const { emitted } = render(SidebarNavItem, {
            props: {
                item: {
                    label: 'Products',
                    icon: 'box',
                    active: false,
                },
            },
        });

        await fireEvent.click(screen.getByRole('button', { name: 'Products' }));

        expect(emitted().select).toEqual([['Products']]);
    });

    it('applies the active state styles', () => {
        render(SidebarNavItem, {
            props: {
                item: {
                    label: 'Dashboard',
                    icon: 'grid',
                    active: true,
                },
            },
        });

        expect(screen.getByRole('button', { name: 'Dashboard' })).toHaveClass('bg-slate-950');
    });
});
