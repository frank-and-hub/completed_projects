interface registerParamsType {
  name: string;
  email: string;
  phone: string;
  password: string;
  confirm_password: string;
  country_code: string;
  country: string;
}

interface verifyOtpParamsType {
  phone: string;
  otp: string;
  verifytype: 'general' | 'forgot' | any;
}

interface loginParamsType {
  email: string;
  password: string;
}

interface setPasswordParamsType {
  password: string;
  confirm_password: string;
  user_id: string;
  verify_token: string;
}
