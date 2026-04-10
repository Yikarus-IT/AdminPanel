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

const deletedProductsResponse = {
    data: {
        data: [
            {
                id: 9,
                name: 'Archived Desk',
                category: 'Furniture',
                sku: 'ARC-001D',
                price: '199.00',
                stock: 0,
                status: 'Urgent',
                description: 'Archived product',
            },
        ],
    },
};

const usersResponse = {
    data: {
        data: [
            {
                id: 1,
                name: 'Luis Ponce',
                email: 'luis@adminpanel.test',
                created_at: '2026-04-01T12:00:00.000000Z',
            },
            {
                id: 2,
                name: 'Andrea Ruiz',
                email: 'andrea@adminpanel.test',
                created_at: '2026-04-01T12:00:00.000000Z',
            },
        ],
    },
};

describe('App', () => {
    beforeEach(() => {
        window.axios = {
            get: vi.fn((url) => {
                if (url === '/products') {
                    return Promise.resolve(productsResponse);
                }

                if (url === '/products?view=deleted') {
                    return Promise.resolve(deletedProductsResponse);
                }

                if (url === '/users') {
                    return Promise.resolve(usersResponse);
                }

                return Promise.reject(new Error(`Unexpected GET ${url}`));
            }),
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
            expect(window.axios.get).toHaveBeenCalledWith('/users');
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

    it('toggles to deleted products and updates the count pill', async () => {
        render(App);

        await waitFor(() => {
            expect(window.axios.get).toHaveBeenCalledWith('/products');
        });

        await fireEvent.click(screen.getByRole('button', { name: 'Products' }));
        await fireEvent.click(screen.getByRole('button', { name: 'View deleted' }));

        await waitFor(() => {
            expect(window.axios.get).toHaveBeenCalledWith('/products?view=deleted');
        });

        expect(await screen.findByRole('heading', { name: 'Review archived products' })).toBeInTheDocument();
        expect(screen.getByText('Archived Desk')).toBeInTheDocument();
        expect(screen.getByText('1 deleted')).toBeInTheDocument();
        expect(screen.getAllByText('Archived')[0]).toBeInTheDocument();
    });

    it('loads users and lets the user create a team member', async () => {
        window.axios.post.mockResolvedValue({
            data: {
                message: 'User created successfully.',
                data: {
                    id: 3,
                    name: 'Camila Torres',
                    email: 'camila@example.com',
                    created_at: '2026-04-02T12:00:00.000000Z',
                },
            },
        });

        render(App);

        await waitFor(() => {
            expect(window.axios.get).toHaveBeenCalledWith('/users');
        });

        await fireEvent.click(screen.getByRole('button', { name: 'Users' }));
        await fireEvent.update(screen.getByLabelText('Name'), 'Camila Torres');
        await fireEvent.update(screen.getByLabelText('Email'), 'camila@example.com');
        await fireEvent.update(screen.getByLabelText('Password'), 'password123');
        await fireEvent.click(screen.getByRole('button', { name: 'Create user' }));

        await waitFor(() => {
            expect(window.axios.post).toHaveBeenCalledWith('/users', {
                name: 'Camila Torres',
                email: 'camila@example.com',
                password: 'password123',
            });
        });

        expect(await screen.findByText('User created successfully.')).toBeInTheDocument();
        expect(screen.getByText('Camila Torres')).toBeInTheDocument();
    });
});
