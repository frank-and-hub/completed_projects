import axios, { CancelTokenSource } from "axios";
import { post_api } from "../root_apis/root_api";

let cancelTokenSource: CancelTokenSource | null = null;

const getMarkerLatAndLong = (payload: {
  latitude: string;
  longitude: string;
  distance: string;
}) => {
  // Cancel the previous request if it exists
  if (cancelTokenSource) {
    cancelTokenSource.cancel("Request canceled due to new request.");
  }

  // Create a new CancelToken
  cancelTokenSource = axios.CancelToken.source();

  return post_api("property-map", payload, {
    cancelToken: cancelTokenSource.token,
  });
};

export { getMarkerLatAndLong };
