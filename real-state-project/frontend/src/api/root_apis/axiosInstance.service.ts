import axios from "axios";

import { axiosError } from "./axiosError.service";
import { store } from "@/store/store";
import { getBaseURl } from "@/utils/createIconUrl";

// const url = 'http://pocket-property.pairroxz.in/api/v1/';
// const url =
//   "https://806b-2402-e280-230c-597-dfe8-fea5-29dd-fac7.ngrok-free.app/api/v1/";
// const url = "http://127.0.0.1:8000/api/v1/";
// const url = 'https://pocketproperty.app/api/v1/';

const axiosInstance = axios.create({
  timeout: 30000,
  timeoutErrorMessage:
    "Network request timed out. The app could not connect to the server. Please make sure you are connected with a good network.",
  headers: {
    // "Content-Type": "multipart/form-data",

    "Content-Type": "application/json",
    Accept: "application/json",
    "ngrok-skip-browser-warning": "hello",
  },
});
axiosInstance.interceptors.request.use(
  (request: any) => {
    try {
      const userStoreData = store.getState()?.userReducer;
      const token = userStoreData?.token;
      const url = getBaseURl() + "/api/v1/";
      // const url =
      //   "https://bc87-2405-201-5c09-1085-8705-af92-847f-b052.ngrok-free.app" +
      //   "/api/v1/";
      request.baseURL = url;
      if (request.headers) {
        if (!request.headers.Authorization) {
          request.headers.Authorization = `Bearer ${token}`;
        }

        // request.headers.lng = language;
      }
      return request;
    } catch (error) {
      console.error("error", error);
    }
  },
  (error) => {
    throw error;
  }
);
// Add a response interceptor
axiosInstance.interceptors.response.use(
  (res) => {
    if ([200, 201].includes(res.status)) {
      return res;
    } else {
      throw Error;
    }
  },
  (err) => axiosError(err)
);
export { axiosInstance };
