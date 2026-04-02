import { fireEvent, render, screen, waitFor } from '@testing-library/vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import App from '../App.vue';

const productsResponse = {
    data: {
        data: [
            {
                id: 1,
                name: 'Starter Kit',
                category: 'Bundles',
                sku: 'KIT-100A',
                price: '129.00',
                stock: 48,
                status: 'Healthy',
                description: 'Onboarding bundle with the most requested workspace essentials.',
            },
            {
                id: 2,
                name: 'Standing Desk',
                category: 'Furniture',
                sku: 'FUR-410D',
                price: '499.00',
                stock: 6,
                status: 'Urgent',
                description: 'Best-selling adjustable desk with premium wood finish.',
            },
        ],
    },
};

describe('App', () => {
    beforeEach(() => {
        window.axios = {
            get: vi.fn().mockResolvedValue(productsResponse),
            post: vi.fn(),
            put: vi.fn(),
            delete: vi.fn(),
        };

        window.confirm = vi.fn(() => true);
    });

    it('loads products and lets the user navigate to the products module', async () => {
        render(App);

        await waitFor(() => {
            expect(window.axios.get).toHaveBeenCalledWith('/products');
        });

        await fireEvent.click(screen.getByRole('button', { name: 'Products' }));

        expect(await screen.findByRole('heading', { name: 'Manage your catalog' })).toBeInTheDocument();
        expect(screen.getByText('Starter Kit')).toBeInTheDocument();
        expect(screen.getByText('KIT-100A')).toBeInTheDocument();
    });

    it('creates a product and shows the success feedback', async () => {
        window.axios.post.mockResolvedValue({
            data: {
                message: 'Product created successfully.',
                data: {
                    id: 3,
                    name: 'Desk Shelf',
                    category: 'Furniture',
                    sku: 'FUR-777S',
                    price: '89.99',
                    stock: 14,
                    status: 'Healthy',
                    description: 'Raised wood shelf for monitor setups.',
                },
            },
        });

        render(App);

        await waitFor(() => {
            expect(window.axios.get).toHaveBeenCalled();
        });

        await fireEvent.click(screen.getByRole('button', { name: 'Products' }));
        await fireEvent.update(screen.getByLabelText('Name'), 'Desk Shelf');
        await fireEvent.update(screen.getByLabelText('Category'), 'Furniture');
        await fireEvent.update(screen.getByLabelText('SKU'), 'FUR-777S');
        await fireEvent.update(screen.getByLabelText('Price'), '89.99');
        await fireEvent.update(screen.getByLabelText('Stock'), '14');
        await fireEvent.update(screen.getByLabelText('Description'), 'Raised wood shelf for monitor setups.');
        await fireEvent.click(screen.getByRole('button', { name: 'Create product' }));

        await waitFor(() => {
            expect(window.axios.post).toHaveBeenCalledWith('/products', {
                name: 'Desk Shelf',
                category: 'Furniture',
                sku: 'FUR-777S',
                price: 89.99,
                stock: 14,
                status: 'Healthy',
                description: 'Raised wood shelf for monitor setups.',
            });
        });

        expect(await screen.findByText('Product created successfully.')).toBeInTheDocument();
        expect(screen.getByText('Desk Shelf')).toBeInTheDocument();
    });
});
