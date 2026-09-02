export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
}

declare module '@inertiajs/core' {
    interface PageProps {
        auth: {
            user: User | null;
        };
        name: string;
    }
}
