import ModalCloseIcon from "@/app/home/components/modalCloseIcon/ModalCloseIcon";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Group, TextInput } from "@mantine/core";
import React from "react";
import useForgotPassword from "./useForgotPassword";

function ForgetPassword({ changeScreenType, handleClose }: changeScreenType) {
  const {
    forgotPasswordHandler,
    isPending,
    form: { getInputProps },
  } = useForgotPassword(changeScreenType);
  return (
    <div className="wd_50_pr forget_card_sc">
      <div className="modal_head_close">
        <h2>Welcome to PocketProperty</h2>
        <ModalCloseIcon handleClose={handleClose} />
      </div>

      <Box className="inner_hight_fm">
        <h4>Enter your Email ID to reset password</h4>
        <TextInput
          label="Email"
          type="email"
          placeholder="Please enter your Email ID"
          mt="sm"
          {...getInputProps("email")}
        />
      </Box>
      <Group className="foot_form">
        <CustomButton
          loading={isPending}
          // onClick={() => changeScreenType("verifyOTP")}
          onClick={() => forgotPasswordHandler()}
        >
          Next
        </CustomButton>
      </Group>
    </div>
  );
}

export default ForgetPassword;
