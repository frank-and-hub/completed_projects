import React, { createContext, useState, useEffect, useContext, useMemo, useCallback } from 'react'
import api from './api'
import config from '../config'
import { get } from './AxiosUtils'
import { useDispatch, useSelector } from 'react-redux'
import { setUser, clearUser } from './../store/authSlice'
import { notifyError } from 'components/admin/comman/notification/Notification'

const AuthContext = createContext();
const url = config.reactApiUrl;
export const AuthProvider = ({ children }) => {

    const dispatch = useDispatch();
    const [loading, setLoading] = useState(false);

    const isLoggedIn = useSelector((state) => (state.auth.isAuthenticated));
    const user = useSelector((state) => (state.auth.user));
    const token = useSelector((state) => (state.auth.token)) ?? localStorage.getItem('token');
    // const token = localStorage.getItem('token');

    const EXPIRATION_TIME = 60 * 60 * 1000; // 3600000 // 1 hour

    const isTokenExpired = () => {
        const expiry_time = localStorage?.getItem('expiry_time');
        return expiry_time ? Date.now() > parseInt(expiry_time, 10) : true;
    };

    const storeUserData = useCallback(async (data, token) => {
        const expiry_time = Date.now() + EXPIRATION_TIME;
        dispatch(setUser({ user: data, token: token }));
        localStorage.setItem('expiry_time', expiry_time.toString());
    }, [dispatch, EXPIRATION_TIME]);

    const clearUserData = useCallback(async () => {
        await dispatch(clearUser());
        localStorage.clear();
        console.trace();
        console.info('Logged out... 👋');
    }, [dispatch]);

    const register = useCallback(async (name, email, password, phone) => {
        try {
            const { data } = await api.post(`${url}/sign-up`, { name, email, password, phone });
            return data;
        } catch (error) {
            notifyError('Registration error:', error);
            throw error;
        }
    }, []);

    const login = useCallback(async (email, password) => {
        try {
            const { data } = await api.post(`${url}/sign-in`, { email, password });
            localStorage.clear();
            await storeUserData(data.user, data?.token);
            localStorage.setItem('token', data?.token);
            localStorage.setItem('user', data?.user);
            localStorage.setItem('role', data?.user?.role?.name);
            localStorage.setItem('user_id', data?.user?._id);
            return data;
        } catch (err) {
            notifyError(`Login error: ${err.message}`);
            throw err;
        }
    }, [storeUserData]);

    const varify_otp = useCallback(async (input, otp, type) => {
        try {
            const { data } = await api.post(`${url}/verify-otp`, { input, otp, type });
            localStorage.clear();
            await storeUserData(data.user, data?.token);
            localStorage.setItem('token', data?.token);
            localStorage.setItem('user', data?.user);
            localStorage.setItem('role', data?.user?.role?.name);
            localStorage.setItem('user-email', data?.user?.email);
            localStorage.setItem('user_id', data?.user?._id);
            return data;
        } catch (err) {
            notifyError(`Login error: ${err.message}`);
            throw err;
        }
    }, [storeUserData]);

    const send_otp = useCallback(async (email, password) => {
        try {
            const { data } = await api.post(`${url}/send-otp`, { email, password });
            localStorage.clear();
            localStorage.setItem('user-email', data?.user?.email);
            localStorage.setItem('user-phone', data?.user?.phone);
            localStorage.setItem('user-login-method', data?.type);
            localStorage.setItem('user-login-otp', data?.otp);
            return data;
        } catch (err) {
            throw err;
        }
    }, []);

    const logout = useCallback(async () => {
        await clearUserData();
    }, [clearUserData]);

    const deepEqual = useCallback((obj1, obj2) => {
        if (obj1 === obj2) return true;

        if (typeof obj1 !== 'object' || typeof obj2 !== 'object' || obj1 == null || obj2 == null) {
            return false;
        }

        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);

        if (keys1.length !== keys2.length) return false;

        for (let key of keys1) {
            if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
                return false;
            }
        }

        return true;
    }, []);

    const loadUser = useCallback(async () => {
        if (token && user) {
            try {
                const { data } = await get(`/users/${user._id}`, token);
                if (!deepEqual(data, user)) {
                    dispatch(setUser({ user: data, token: data.token }));
                }
            } catch (err) {
                console.error(err.message);
                logout();
            }
        }
        setLoading(false)
    }, [token, user, dispatch, deepEqual, logout]);

    useEffect(() => {
        if (localStorage?.getItem('expiry_time') && isTokenExpired()) {
            console.error(`Token expired!`);
            logout();
        } else {
            loadUser();
        }
    }, [logout, loadUser]);

    const contextValue = useMemo(() => ({
        user,
        register,
        login,
        logout,
        loadUser,
        send_otp,
        varify_otp,
        isLoggedIn,
        loading,
    }), [user, register, login, logout, loadUser, send_otp, varify_otp, isLoggedIn, loading]);

    return (
        <AuthContext.Provider value={contextValue}>
            {!loading && children}
        </AuthContext.Provider >
    );
};

export const useAuth = () => useContext(AuthContext);