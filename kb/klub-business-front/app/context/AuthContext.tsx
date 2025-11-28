"use client";

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { get } from '@/utils/axios';
import { useRouter } from 'next/navigation';
import { getToken, setToken } from '@/utils/useAuth';
import { store } from '@/store';
import { cl } from '@/utils/console';

interface AuthContextType {
    user: any | null;
    loading: boolean;
    token: string | null;
    setLoading: (loading: boolean) => void;
    pagetitle: string | null;
    setPageTitle: (pagetitle: string) => void;
}

const AuthContext = createContext<AuthContextType>({
    user: null,
    loading: true,
    token: null,
    setLoading: () => { },
    pagetitle: null,
    setPageTitle: () => { },
});

interface AuthProviderProps {
    children: ReactNode;
}

export default function AuthProvider({ children }: AuthProviderProps) {
    const [user, setUser] = useState<any | null>(null);
    const [loading, setLoading] = useState(true);
    const router = useRouter();
    const [pagetitle, setPageTitle] = useState<string | null>(null);
    const token = getToken();

    useEffect(() => {
        const fetchUser = async () => {
            try {
                const data = await get('v1/users/profile');
                setUser(data.user ?? null);
            } catch (error) {
                setUser(null);
                router.push('/auth/sign-in');
            } finally {
                setLoading(false);
            }
        };
        cl(`user data: ${user}`, `this is a user token: ${JSON.stringify(token)}`);
        if (token && !user) {
            fetchUser();
        } else {
            router.push('/auth/sign-in');
        }
        setToken(token);
    }, [user, token]);

    return (
        <AuthContext.Provider value={{ user, loading, token, setLoading, pagetitle, setPageTitle }}>
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) throw new Error('useAuth must be used within an AuthProvider');
    return context;
};
