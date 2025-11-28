import { get_api, post_api } from '../root_apis/root_api';
interface signUpLandlordParamsType {
  name: string;
  email: string;
  phone: string;
  password: string;
  confirm_password: string;
  country_code: string;
  image: string;
  country: string;
}
const signupAsLandlord = ({ data }: { data: FormData }) =>
  post_api('privatelandlord-signup', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });

const landlordOtpVerify = (payload: {
  verifytype: 'email' | 'phone';
  phone?: string;
  email?: string;
  otp: string;
}) => post_api('privatelandlord-verify', payload);

const landLordResendOtp = (payload: {
  verifytype: 'phone' | 'email';
  phone?: string;
  email?: string;
}) => post_api('privatelandlord-resend-otp', payload);

export { signupAsLandlord, landlordOtpVerify, landLordResendOtp };
