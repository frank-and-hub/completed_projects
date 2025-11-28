interface email_verified_atType {}

interface otp_verificationType {
  id: string;
  user_id: string;
  phone: string;
  otp: string;
  otp_generated_at: string;
  otp_verified_at: string;
  created_at: string;
  updated_at: string;
}

interface loginDetailType {
  id: string;
  name: string;
  phone: string;
  email: string;
  type: string;
  email_verified_at: email_verified_atType;
  created_at: string;
  updated_at: string;
  otp_verification: otp_verificationType;
  country_code: string;
}

interface loginType {
  status: boolean;
  message: string;
  data: loginDetailType;
}
