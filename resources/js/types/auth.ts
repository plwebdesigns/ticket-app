export type Role = {
    id: number;
    name: string;
    description: string | null;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role_id?: number;
    role?: Role | null;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    isAdmin: boolean;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
