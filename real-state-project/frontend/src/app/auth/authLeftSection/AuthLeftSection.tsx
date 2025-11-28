import React from "react";
import signupImage from "../../../../assets/svg/new_account.svg";
import loginImage from "../../../../assets/svg/login_amico.svg";
import CreatePasswordImage from "../../../../assets/svg/create_password.svg";
import VerifyOtpImage from "../../../../assets/svg/verify_otp.svg";
import VerificationfailedImage from "../../../../assets/svg/verificationfailed.svg";

import Image from "next/image";
type IObjectKeys = {
  [key in screenType]: any;
};

const data: IObjectKeys = {
  signup: {
    heading: "Hey there!",
    subHeading: `New to PocketProperty? Join now!`,
    image: signupImage,
  },
  chooseUserType: {
    heading: "Hey there!",
    subHeading: `Let's get you logged in.`,
    image: loginImage,
  },
  landlordSignUp: {
    heading: "Hey there!",
    subHeading: `New to PocketProperty? Join now!`,
    image: signupImage,
  },
  agentSignUp: {
    heading: "Hey there!",
    subHeading: `New to PocketProperty? Join now!`,
    image: signupImage,
  },
  login: {
    heading: "Welcome Back!",
    subHeading: `Let's get you logged in.`,
    image: loginImage,
  },
  agentLogin: {
    heading: "Hey there!",
    subHeading: `Welcome aboard! New? Join now!`,
    image: signupImage,
  },
  createPassword: {
    heading: "Secure your account",
    subHeading: `Create your password to proceed.`,
    image: CreatePasswordImage,
  },
  forgetPassword: {
    heading: "Lost your key?",
    subHeading: ` Let's reset your password.`,
    image: loginImage,
  },
  verifyOTP: {
    heading: "One Last Step",
    subHeading: `For Advance Security`,
    image: VerifyOtpImage,
  },
  verificationFailed: {
    heading: "Oops! Incorrect Code",
    subHeading: `Let's try that again.`,
    image: VerificationfailedImage,
  },
  profile: {},
  search: {},
};

function AuthLeftSection({ type = "signup" }: { type: screenType }) {
  return (
    <div className="comman_form_detail_left">
      <figcaption>
        <h1>{data?.[type]?.heading}</h1>
        <h6>{data?.[type]?.subHeading}</h6>
      </figcaption>
      <div className="lrft_from_icons">
        <Image src={data?.[type]?.image} alt="PocketProperty" />
      </div>
    </div>
  );
}

export default AuthLeftSection;
