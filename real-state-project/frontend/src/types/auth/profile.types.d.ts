interface profileUpdateParamsType {
  data: FormData;
}
interface profileUpdatePasswordParamsType {
  old_password: string;
  new_password: string;
  confirm_password: string;
}
interface messageAlertDataType {
  image: string;
  StartTime: string;
  EndTime: string;
  id: number;
  start_value_time: string;
  end_value_time: string;
  schedule_type: string;
}
interface ProfileDetailType {
  id: string;
  name: string;
  phone: string;
  email: string;
  image: string;
  country_code: string;
  subscription: string;
  subscription_type: "Basic" | string;
  total_request: number;
  schedule_type: string;

  message_alert: number;
  login_type: string;
  country: string;
  user_employment?: {
    emplyee_type?: "job" | "employer";
    live_with?: number;
    user_id?: string;
  };
}

interface dataType {
  type: "verify" | "update";
  email?: string;
  verifytype: "both" | "onlyphone" | "onlypass";
}

interface profileUpdateResponseType {
  status: boolean;
  message: string;
  data: dataType;
}

interface profileUpdateResponseType {
  status: boolean;
  message: string;
  data: dataType;
}

interface profileType {
  status: boolean;
  message: string;
  data: dataType;
}
