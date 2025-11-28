import axios from 'axios';
import { post_api } from '../root_apis/root_api';

const register = (data: registerParamsType) =>
  post_api('register', { ...data });

const otpVerify = (data: verifyOtpParamsType) =>
  post_api('otp-verify', { ...data });

export { register, otpVerify };
