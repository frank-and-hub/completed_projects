import { post_api } from '../root_apis/root_api';

const authResendOtp = (data: { phone: string }) =>
  post_api('resend-otp', { ...data });

const profileResendOtp = (data: {
  country_code: string;
  phone: string;
  verifytype: string;
}) => post_api('profile-resend-otp', data);

export { authResendOtp, profileResendOtp };
