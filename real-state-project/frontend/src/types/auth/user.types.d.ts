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

interface userType {
  id: string;
  name: string;
  phone: string;
  email: string;
  type: string;
  email_verified_at: email_verified_atType;
  created_at: string;
  updated_at: string;
  otp_verification: otp_verificationType;
  subscription: string;
}
interface userInformationType {
  id: string;
  name: string;
  phone: string;
  email: string;
  image: string;
  country_code: string;
  subscription: string;
  subscription_type: "Basic" | string;
  message_alert: number;
  total_request: number;
  schedule_type: string;
  login_type: str;
  country: string;
  location?: {
    lat: number;
    lng: number;
  };
  subscription_expired_date?: string;
  pending_request_count?: number;
  credit_report?: {
    id: string;
    status?: boolean;
    url?: string;
  };
  user_employment?: {
    emplyee_type?: "job" | "employer";
    live_with?: number;
    user_id?: string;
  };
}

interface userDetailType {
  token: string;
  user: userInformationType;
  verify_token: string;
}

interface userDetailElementType {
  status: boolean;
  message: string;
  data: userDetailType;
}
