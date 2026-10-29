export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};

export type ApplicationStatus =
    | 'wishlist'
    | 'applied'
    | 'interviewing'
    | 'offer'
    | 'rejected';

export interface StatusOption {
    value: ApplicationStatus;
    label: string;
}

export interface Application {
    id: number;
    company: string;
    role: string;
    url: string | null;
    location: string | null;
    salary: string | null;
    status: ApplicationStatus;
    status_label: string;
    position: number;
    applied_on: string | null;
}
