import { Button, Group } from "@mantine/core";
import { cloneElement, useState } from "react";
import ModalCloseIcon from "../home/components/modalCloseIcon/ModalCloseIcon";
import Profile from "../profile/Profile";
import AuthRightSection from "./AuthRightSection";
import CreatePassword from "./createPassword/CreatePassword";
import ForgetPassword from "./forgetPassword/ForgetPassword";
import Login from "./login/Login";
import Signup from "./signup/Signup";
import VerificationFailed from "./verificationFailed/VerificationFailed";
import VerifyOtp from "./verifyOtp/VerifyOtp";
import AuthLeftSection from "./authLeftSection/AuthLeftSection";

function NewAuthModal({ type, handleClose }: IAuthModal) {
  const [popupType, setPopupType] = useState<screenType>(type);
  const handleChangeScreenType: changeScreenTypeFunction = (
    value,
    closeModal
  ) => {
    if (closeModal) {
      return handleClose && handleClose();
    }
    setPopupType(value);
  };

  const renderComponent = (value: screenType) => {
    switch (value) {
      case "signup":
        return <Signup changeScreenType={handleChangeScreenType} />;
      case "verifyOTP":
        return <VerifyOtp changeScreenType={handleChangeScreenType} />;
      case "forgetPassword":
        return <ForgetPassword changeScreenType={handleChangeScreenType} />;
      case "createPassword":
        return <CreatePassword changeScreenType={handleChangeScreenType} />;
      case "verificationFailed":
        return <VerificationFailed changeScreenType={handleChangeScreenType} />;
      case "profile":
        return <Profile changeScreenType={handleChangeScreenType} />;
      default:
        return <Login changeScreenType={handleChangeScreenType} />;
    }
  };
  return (
    <Group wrap="nowrap" className="inner_modal_card requirements_modal_card">
      {popupType === "login" || popupType === "signup" ? null : (
        <AuthLeftSection type={popupType} />
      )}
      {popupType === "login" || popupType === "signup" ? (
        <div className="comman_form_detail">
          <div className="modal_head_close">
            <h2>Create New account or Login to Proceed Further</h2>
            <ModalCloseIcon handleClose={handleClose} />
          </div>

          <div className="swich_to_form">
            <Button
              variant="transparent"
              onClick={() => handleChangeScreenType("login")}
              className={popupType === "login" ? "active" : "active_login"}
            >
              Login
            </Button>
            <Button
              variant="transparent"
              onClick={() => handleChangeScreenType("signup")}
              className={popupType === "signup" ? "active" : "active_login"}
            >
              New Account
            </Button>
          </div>

          {cloneElement(renderComponent(popupType), {
            handleClose,
          })}
        </div>
      ) : (
        cloneElement(renderComponent(popupType), {
          handleClose,
        })
      )}
      {popupType === "login" || popupType === "signup" ? (
        <AuthRightSection />
      ) : null}
    </Group>
  );
}

export default NewAuthModal;
