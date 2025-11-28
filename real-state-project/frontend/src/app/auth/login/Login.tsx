import { googleLogin } from "@/api/auth/login";
import CustomButton from "@/components/customButton/CustomButton";
import { useAppDispatch } from "@/store/hooks";
import { updateToken } from "@/store/reducer/userReducer";
import {
  Box,
  Button,
  Checkbox,
  Grid,
  Group,
  PasswordInput,
  TextInput,
} from "@mantine/core";
import { useGoogleLogin } from "@react-oauth/google";
import { useMutation } from "@tanstack/react-query";
import Image from "next/image";
import ReactLoginMS from "react-ms-login";
import GoogleSvg from "../../../../assets/svg/google_icon.svg";
import OutlookSvg from "../../../../assets/svg/outlook_icon.svg";
import "./login.scss";
import useLogin from "./useLogin";
import { getBaseURl } from "@/utils/createIconUrl";
function Login({
  changeScreenType,
  handleClose,
  type = "tenant",
}: changeScreenType) {
  const {
    form: { getInputProps },
    handleSubmit,
    isPending,
    contextValue: { propertySearchData },
    isPropertySearch,
    searchMutate,
    setIsModalOpen,
    rememberMe,
    setRememberMe,
  } = useLogin(changeScreenType, handleClose);

  const dispatch = useAppDispatch();
  const { mutate } = useMutation({
    mutationFn: googleLogin,
    onSuccess: (data: userDetailElementType) => {
      dispatch(updateToken(data?.data));
      handleClose && handleClose();
      if (!data?.data?.user?.phone) {
        setIsModalOpen("authModal");
      } else {
        if (data?.data?.user?.subscription && isPropertySearch) {
          searchMutate({
            ...propertySearchData,
          });
        } else if (!data?.data?.user?.subscription && isPropertySearch) {
          setIsModalOpen("selectPlan");
        }
      }
    },
  });

  const login = useGoogleLogin({
    onSuccess: (tokenResponse) => {
      mutate({
        token: tokenResponse?.access_token,
        social_type: "google",
      });
    },
  });

  return (
    <div className="login">
      {type === "tenant" ? (
        <>
          <Group className="drt_to_login">
            <Button
              variant="default"
              onClick={() => {
                login();
              }}
            >
              Google <Image src={GoogleSvg} alt="" />
            </Button>

            <span className="or">OR</span>

            <Box className="outlook_button_click">
              <ReactLoginMS
                clientId="a8c6942d-7f2f-4549-ba91-18492feaf998"
                // redirectUri="http://localhost:3000/api/v1/microsoft-callback"
                redirectUri={getBaseURl() + "/api/v1/microsoft-callback"}
                cssClass="ms-login"
                btnContent="Outlook"
                responseType="token"
                handleLogin={(data: any) => {}}
              />
              <Image src={OutlookSvg} alt="" />
            </Box>
          </Group>
          <div className="or_cun_with">
            <span>Or continue with Email ID</span>
          </div>
        </>
      ) : null}

      <Box className="inner_fm_fill" component="form" maw={400} mx="auto">
        <Box className="inner_hight_fm">
          <TextInput
            type="email"
            placeholder="Please enter your Email ID"
            mt="sm"
            {...getInputProps("email")}
          />
          {type === "tenant" ? (
            <PasswordInput
              mt="sm"
              label="Password"
              placeholder="Please enter your  Password"
              {...getInputProps("password")}
            />
          ) : (
            <TextInput
              label="Agent ID"
              placeholder="Please enter your agent ID"
              mt="sm"
              {...getInputProps("email")}
            />
          )}
          {type === "agent" ? (
            <Grid>
              <Grid.Col span={6}>
                <PasswordInput
                  mt="sm"
                  label="Password"
                  placeholder="Create your Password"
                  {...getInputProps("password")}
                />
              </Grid.Col>
              <Grid.Col span={6}>
                <PasswordInput
                  mt="sm"
                  label="Confirm password"
                  placeholder="Please re-enter your Password"
                  {...getInputProps("confirm_password")}
                />
              </Grid.Col>
            </Grid>
          ) : null}
          {type === "tenant" ? (
            <Group className="foot_checkbox">
              <Checkbox
                checked={rememberMe}
                label="Remember  Me"
                onChange={(event) => {
                  setRememberMe(event.currentTarget.checked);
                }}
              />
              <Button
                variant="transparent"
                onClick={() => changeScreenType("forgetPassword")}
              >
                Forgot Password?
              </Button>
            </Group>
          ) : null}
        </Box>

        <Group className="foot_form">
          <CustomButton
            loading={isPending}
            onClick={() => {
              handleSubmit();
            }}
          >
            {type === "agent" ? "SignUp" : "Login"}
          </CustomButton>
        </Group>
      </Box>
    </div>
  );
}

export default Login;
