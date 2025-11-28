import { AxiosRequestConfig } from 'axios';
import { axiosInstance } from './axiosInstance.service';

export const get_api = (route: string, headers?: AxiosRequestConfig<Headers>) =>
  axiosInstance.get(route, headers).then((res: any) => {
    return res?.data?.data;
  });
export const get_api_data = (
  route: string,
  headers?: AxiosRequestConfig<Headers>
) =>
  axiosInstance.get(route, headers).then((res: any) => {
    return res?.data;
  });
export const post_api = (
  route: string,
  data?: any,
  headers?: AxiosRequestConfig<Headers>
) =>
  axiosInstance.post(route, data, headers).then((res: any) => {
    return res?.data;
  });
export const patch_api = (
  route: string,
  data: any,
  headers?: AxiosRequestConfig<Headers>
) => axiosInstance.patch(route, data, headers).then((res: any) => res?.data);
export const delete_api = (
  route: string,
  data?: any,
  headers?: AxiosRequestConfig<Headers>
) =>
  axiosInstance
    .delete(route, {
      data,
      ...headers,
    })
    .then((res: any) => res?.data);

export const post_form_data = (
  route: string,
  data: any,
  headers?: AxiosRequestConfig<Headers>
) =>
  axiosInstance
    .post(route, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      ...headers,
    })
    .then((res: any) => res?.data);
