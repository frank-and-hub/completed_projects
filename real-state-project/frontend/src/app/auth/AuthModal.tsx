"use client";
import { Button, Group } from "@mantine/core";
import { cloneElement, useEffect, useState } from "react";
import ModalCloseIcon from "../home/components/modalCloseIcon/ModalCloseIcon";
import Profile from "../profile/Profile";
import AuthLeftSection from "./authLeftSection/AuthLeftSection";
import CreatePassword from "./createPassword/CreatePassword";
import ForgetPassword from "./forgetPassword/ForgetPassword";
import Login from "./login/Login";
import Signup from "./signup/Signup";
import VerificationFailed from "./verificationFailed/VerificationFailed";
import VerifyOtp from "./verifyOtp/VerifyOtp";
import { useAppDispatch } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
} from "@/store/reducer/userReducer";
import ChooseUserType from "./chooseUserType/ChooseUserType";
import CustomButton from "@/components/customButton/CustomButton";
import axios from "axios";

function AuthModal({ type, handleClose, isClose = true }: IAuthModal) {
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
  const dispatch = useAppDispatch();
  useEffect(() => {
    dispatch(updatePropertyInformation(undefined));
    dispatch(updatePropertySearch(false));
  }, []);
  const [isNext, setIsNext] = useState<boolean>(false);

  const renderComponent = (value: screenType) => {
    switch (value) {
      case "chooseUserType":
        return <ChooseUserType changeScreenType={handleChangeScreenType} />;
      case "signup":
        return <Signup changeScreenType={handleChangeScreenType} />;
      case "verifyOTP":
        return (
          <VerifyOtp
            changeScreenType={handleChangeScreenType}
            isClose={isClose}
          />
        );
      case "forgetPassword":
        return <ForgetPassword changeScreenType={handleChangeScreenType} />;
      case "landlordSignUp":
        return (
          <Signup
            changeScreenType={handleChangeScreenType}
            type="landLord"
            setIsNext={setIsNext}
            isNext={isNext}
          />
        );
      case "agentSignUp":
        return (
          <Signup changeScreenType={handleChangeScreenType} type="agent" />
        );

      case "createPassword":
        return <CreatePassword changeScreenType={handleChangeScreenType} />;
      case "agentLogin":
        return <Login changeScreenType={handleChangeScreenType} type="agent" />;
      case "verificationFailed":
        return (
          <VerificationFailed
            changeScreenType={handleChangeScreenType}
            isClose={isClose}
          />
        );
      case "profile":
        return (
          <Profile
            changeScreenType={handleChangeScreenType}
            isClose={isClose}
          />
        );
      default:
        return <Login changeScreenType={handleChangeScreenType} />;
    }
  };
  return (
    <Group wrap="nowrap" className="inner_modal_card requirements_add_modal">
      {popupType === "profile" ? null : isNext ? null : (
        <AuthLeftSection type={popupType} />
      )}
      {popupType === "login" ||
      popupType === "signup" ||
      popupType === "landlordSignUp" ||
      popupType === "agentSignUp" ||
      popupType === "agentLogin" ||
      popupType === "chooseUserType" ? (
        <div className={`comman_form_detail ${isNext ? "face_capture" : ""}`}>
          <div className="modal_head_close">
            <h2>
              {popupType === "chooseUserType"
                ? "Select who you are "
                : "Welcome to PocketProperty"}
            </h2>
            <ModalCloseIcon handleClose={handleClose} />
          </div>

          {popupType === "chooseUserType" ? null : popupType ===
            "landlordSignUp" ? (
            <h2 style={{ marginBottom: 10 }}>
              {isNext ? "Step 2" : "Sign up as a Private Landlord"}
            </h2>
          ) : (
            <div className="swich_to_form">
              <Button
                variant="transparent"
                onClick={() =>
                  handleChangeScreenType(
                    type === "agentSignUp" ? "agentSignUp" : "login"
                  )
                }
                className={
                  popupType === "login" || popupType === "agentSignUp"
                    ? "active"
                    : "active_login"
                }
              >
                {type === "agentSignUp" ? "New Agent" : "Login"}
              </Button>

              <Button
                variant="transparent"
                onClick={() =>
                  handleChangeScreenType(
                    type === "agentSignUp" ? "agentLogin" : "signup"
                  )
                }
                className={
                  popupType === "signup" || popupType === "agentLogin"
                    ? "active"
                    : "active_login"
                }
              >
                {type === "agentSignUp" ? "Already an agent" : "New Account"}
              </Button>
            </div>
          )}

          {cloneElement(renderComponent(popupType), {
            handleClose,
          })}
        </div>
      ) : (
        cloneElement(renderComponent(popupType), {
          handleClose,
        })
      )}
    </Group>
  );
}

export default AuthModal;
