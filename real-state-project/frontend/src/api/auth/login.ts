import { get_api, post_api } from "../root_apis/root_api";

const login = (data: loginParamsType) => post_api("login", { ...data });
const logOut = () => post_api("logout");
// const googleLogin = () => get_api('google-login');
const googleLogin = (data: {
  token: any;
  social_type: "google" | "microsoft";
}): Promise<userDetailElementType> => post_api("social-login", data);
const outLookLogin = () => get_api("microsoft-login");

const createAWSSession = (data?: any) =>
  post_api("face-detect/create-session", data);

const checkValidSession = (data: {
  sessionId: string;
}): Promise<{
  data: { confidence: number; status: "SUCCEEDED" | string };
  path: string;
}> => post_api("face-detect/check-valid-session", data);

export {
  login,
  logOut,
  googleLogin,
  outLookLogin,
  createAWSSession,
  checkValidSession,
};
