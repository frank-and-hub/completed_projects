import ModalCloseIcon from "@/app/home/components/modalCloseIcon/ModalCloseIcon";
import CustomButton from "@/components/customButton/CustomButton";
import { Anchor, Group, Loader, PinInput } from "@mantine/core";
import "./../verifyOtp/verifyotp.scss";
import React, { useMemo } from "react";
import useVerificationFailed from "./useVerificationFailed";
import OtpTimer from "@/utils/OtpTimer";
import { maskEmail } from "@/utils/maskEmail";

function VerificationFailed({
  changeScreenType,
  handleClose,
  isClose = true,
}: changeScreenType) {
  const {
    form: {
      getInputProps,
      values: { otp },
    },
    handleSubmit,
    isPending,
    phone,
    contextValue: {
      otpVerificationType,
      profileThings: { countryCode },
      email,
      isLandLord,
    },
    profileOtpLoading,
    isResend,
    setIsResend,
    profileResendLoading,
    resendLoading,
    resendOtp,
    other,
    landLordResendOtpLoading,
    landlordLoading,
  } = useVerificationFailed({
    changeScreenType,
    handleClose,
    isClose: isClose,
  });

  return (
    <div className="wd_50_pr verify_card_sc">
      <div className="modal_head_close">
        <h2>Code Verification Failed</h2>
        <ModalCloseIcon
          handleClose={() => {
            if (isClose) {
              handleClose && handleClose();
            }
          }}
        />
      </div>
      <p
        className="verification_faild_msg"
        style={{
          color: "#F30051",
        }}
      >
        Sorry, the OTP you entered is incorrect. Please try again.
      </p>

      <Group className="otp_sent_num">
        <h5>
          {otpVerificationType === "mobile"
            ? "Enter the OTP sent to your WhatsApp number"
            : other === "mobile"
            ? "Enter the OTP sent to your WhatsApp number"
            : "Enter OTP sent to your Email ID"}
        </h5>
        <h6>
          Ending with{" "}
          <span>
            {otpVerificationType === "mobile"
              ? `${countryCode} ******${phone[phone.length - 4]}${
                  phone[phone.length - 3]
                }${phone[phone.length - 2]}${phone[phone.length - 1]}`
              : other === "mobile"
              ? `${countryCode} ******${phone[phone.length - 4]}${
                  phone[phone.length - 3]
                }${phone[phone.length - 2]}${phone[phone.length - 1]}`
              : maskEmail(email)}
          </span>
        </h6>
      </Group>
      <Group className="input_ui_otp">
        <PinInput
          size="xl"
          type={/^[0-9]*$/}
          inputType="tel"
          inputMode="numeric"
          {...getInputProps("otp")}
        />
      </Group>
      <Group className="foot_form">
        <CustomButton
          disabled={!(otp.length === 4)}
          loading={
            isLandLord
              ? landlordLoading
              : otpVerificationType === "mobile"
              ? isPending
              : profileOtpLoading
          }
          onClick={() => handleSubmit()}
        >
          Authenticate
        </CustomButton>
      </Group>

      <Group className="otp_sent_seccond">
        <h6>Didn't receive the OTP? </h6>
        {isResend ? (
          <h6>
            <Anchor
              href="javascript:;"
              underline="always"
              onClick={() => {
                setIsResend(false);
                resendOtp();
              }}
            >
              Click Here
            </Anchor>
            to Resend
          </h6>
        ) : (
            isLandLord
              ? landLordResendOtpLoading
              : otpVerificationType === "email"
              ? profileResendLoading
              : resendLoading
          ) ? (
          <Loader size={20} />
        ) : (
          <OtpTimer
            onCountDownEnd={() => {
              setIsResend(true);
            }}
            initialValue={60}
          />
        )}
      </Group>
    </div>
  );
}

export default VerificationFailed;
