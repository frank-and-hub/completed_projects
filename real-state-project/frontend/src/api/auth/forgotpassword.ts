import { post_api } from '../root_apis/root_api';

interface forgotPasswordParamsType {
  email: string;
}
const forgotPassword = (data: forgotPasswordParamsType) =>
  post_api('forgot-password', { ...data });

const setNewPassword = (data: setPasswordParamsType) =>
  post_api('set-password', { ...data });

export { forgotPassword, setNewPassword };
