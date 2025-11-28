import { AxiosError } from "axios";
import { store } from "@/store/store";
import { notification } from "@/utils/notification";
import axios from "axios";

export const axiosError = async (err: AxiosError<any>) => {
  if (axios.isCancel(err)) {
    // Handle request cancellation
    console.warn("Request canceled:", err.message); // Optionally log the cancellation reason
    throw err;
  }

  const axiosError = err as AxiosError<any>;
  if (axiosError.response) {
    // The request was made and the server responded with a status code
    if (axiosError?.response?.status === 401) {
      notification({
        title: "Session Expired!",
        message:
          "Your login session has expired. Please login again to continue.",
        type: "error",
      });
      store.dispatch({
        type: "LOGOUT",
      });
    } else if (axiosError.response.status === 404) {
      notification({
        title: "Error",
        message: "Resource not found.",
        type: "error",
      });
    } else {
      notification({
        title: "Error",
        // message: "An error occurred while processing your request.",
        message: axiosError?.response?.data?.message,
        type: "error",
      });
    }
  } else if (axiosError.request) {
    // The request was made but no response was received
    notification({
      title: "Server Error!",
      message: "No response received from the server. Please try again later.",
      type: "error",
    });
  } else {
    // Something happened in setting up the request that triggered an error
    notification({
      title: "Error",
      message: "An error occurred while setting up the request.",
      type: "error",
    });
  }

  throw err;
};
