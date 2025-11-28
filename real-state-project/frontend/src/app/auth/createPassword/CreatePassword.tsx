import ModalCloseIcon from "@/app/home/components/modalCloseIcon/ModalCloseIcon";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Group, PasswordInput } from "@mantine/core";
import useCreatePassword from "./useCreatePassword";

function CreatePassword({ changeScreenType, handleClose }: changeScreenType) {
  const {
    handleNewPassword,
    isPending,
    form: { getInputProps },
    contextValue: { otpVerificationType },
    profileUpdateLoading,
  } = useCreatePassword({ changeScreenType, handleClose });

  return (
    <div className="wd_50_pr verify_card_sc">
      <div className="modal_head_close">
        <h2>Set your Password </h2>
        <ModalCloseIcon handleClose={handleClose} />
      </div>
      <p className="verification_faild_msg">
        Please set and confirm your new password.
      </p>

      <Box className="inner_hight_fm">
        <div className="inner_hight_fm">
          {otpVerificationType === "email" ? (
            <PasswordInput
              label="Old Password"
              placeholder="*************"
              {...getInputProps("oldPassword")}
            />
          ) : null}
          <PasswordInput
            label="New Password"
            placeholder="*************"
            {...getInputProps("password")}
          />

          <PasswordInput
            mt="sm"
            label="Confirm New Password"
            placeholder="*************"
            {...getInputProps("confirm_password")}
          />
        </div>

        <Group className="foot_form">
          <CustomButton
            loading={
              otpVerificationType === "email" ? profileUpdateLoading : isPending
            }
            onClick={() => {
              handleNewPassword();
            }}
          >
            Set Password
          </CustomButton>
        </Group>
      </Box>
    </div>
  );
}

export default CreatePassword;
