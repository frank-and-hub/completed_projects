import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';
import { sleep, tostingError } from '@/utils/helpers';
import { getToken, logout } from '@/utils/useAuth';
// import { toast } from 'react-toastify';

export const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://10.59.145.26:5080/api/';

const api = axios.create({
  baseURL: baseUrl,
  timeout: 30000,
  timeoutErrorMessage: "Network request timed out. Please check your connection.",
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  withCredentials: true,
});

api.interceptors.request.use(
  (config) => {
    const token = getToken();
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
  },
  (error) => Promise.reject(error)
);

interface RequestProps {
  method: 'get' | 'post' | 'put' | 'patch' | 'delete';
  url: string;
  data?: object;
  retries?: number;
  showToast?: boolean;
  successMessage?: string;
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      logout();
      window.location.href = '/auth/sign-in';
    }
    return Promise.reject(error);
  }
);

const request = async <T = any>({
  method,
  url,
  data = {},
  retries = 0,
  // showToast = false,
  // successMessage = 'Success!',
}: RequestProps & { successMessage?: string }): Promise<any> => {
  // const toastId = toast.loading('Processing...', { autoClose: 2000 });

  try {
    const config: AxiosRequestConfig = {
      method,
      url,
      ...(method !== 'get' ? { data } : {}),
    };
    const response: AxiosResponse<T> = await api(config);
    // if (response) {
    //   showToast ? toast.update(toastId, {
    //     render: successMessage,
    //     type: 'success',
    //     isLoading: false,
    //     autoClose: 2000,
    //     closeOnClick: true,
    //   }) : toast.dismiss(toastId);
    // }
    return response.data;
  } catch (error: any) {
    const status = error?.response?.status;

    if (retries > 0) {
      await sleep(500);
      console.warn(`Retrying ${method.toUpperCase()} ${url} (${retries - 1} left)`);
      return request<T>({ method, url, data, retries: retries - 1 });
    }

    // toast.update(toastId, {
    //   render: `Error ${status || ''}: ${error?.response?.data?.message || error?.message || `Something went wrong!`}`,
    //   type: 'error',
    //   isLoading: false,
    //   autoClose: 1500,
    // });

    tostingError(status);
    throw error?.response?.data?.message || error?.message || error;
  }
};

api.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
);

const get = <T = any>(url: string, params?: object) => request<T>({ method: 'get', url: params ? `${url}?${new URLSearchParams(params as any)}` : url });
const post = <T = any>(url: string, data: object) => request<T>({ method: 'post', url, data});
const put = <T = any>(url: string, data: object) => request<T>({ method: 'put', url, data});
const patch = <T = any>(url: string, data: object) => request<T>({ method: 'patch', url, data});
const destroy = <T = any>(url: string) => request<T>({ method: 'delete', url });

export { get, post, put, patch, destroy };
