type screenType =
  | "login"
  | "signup"
  | "forgetPassword"
  | "verifyOTP"
  | "createPassword"
  | "verificationFailed"
  | "profile"
  | "search"
  | "landlordSignUp"
  | "agentSignUp"
  | "agentLogin"
  | "chooseUserType";

type modalType =
  | "selectPlan"
  | ""
  | "logout"
  | "thankYou"
  | "plan"
  | "login"
  | "advanceFilter"
  | "message alert"
  | "payment"
  | "planThank"
  | "messageAlert"
  | "authModal";

interface IAuthModal {
  type: screenType;
  handleClose?: () => void;
  isFromSearch?: boolean;
  isClose?: boolean;
}

type changeScreenTypeFunction = (
  value: screenType,
  closePopup?: boolean
) => void;

interface changeScreenType {
  changeScreenType: changeScreenTypeFunction;
  handleClose?: () => void;
  isClose?: boolean;
  type?: "landLord" | "agent" | "tenant";
  setIsNext?: (value: boolean) => void;
  isNext?: boolean;
}
