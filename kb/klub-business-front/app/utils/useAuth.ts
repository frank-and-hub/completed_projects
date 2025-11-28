import { store } from "@/store";
import { setTokenFromStorage, logedIn, logedOut } from "@/store/slices/authSlice";

export const token = typeof window !== 'undefined' ? localStorage?.getItem('token') : null;

export const getToken = (): string | null => store.getState().auth.token;

export const login = (token: string): any => {
    return store.dispatch(logedIn(token))
};

export const logout = (): any => {
    return store.dispatch(logedOut())
};

export const setToken = (token: string | null): any => {
    return store.dispatch(setTokenFromStorage(token))
};