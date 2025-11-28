import { googleLogin } from "@/api/auth/login";
import { getAllCountries } from "@/api/propertySearchHistory/propertySearch";
import dynamic from "next/dynamic";

import CustomApiSelect from "@/components/customApiSelect/CustomApiSelect";
import CustomButton from "@/components/customButton/CustomButton";
import { useAppDispatch } from "@/store/hooks";
import { updateToken } from "@/store/reducer/userReducer";
import { getBaseURl } from "@/utils/createIconUrl";
import {
  Box,
  Button,
  Center,
  Grid,
  Group,
  PasswordInput,
  TextInput,
} from "@mantine/core";
import { useGoogleLogin } from "@react-oauth/google";
import { useMutation } from "@tanstack/react-query";
import Image from "next/image";
import { useState } from "react";
import ReactLoginMS from "react-ms-login";
import "react-phone-input-2/lib/style.css";
import GoogleSvg from "../../../../assets/svg/google_icon.svg";
import OutlookSvg from "../../../../assets/svg/outlook_icon.svg";
import "./signup.scss";
import useSignup from "./useSignup";

const LivenessQuickStartReact = dynamic(
  () =>
    import("@/app/home/LivenessQuickStartReact").then(
      (mod) => mod.LivenessQuickStartReact
    ),
  {
    ssr: false,
    loading: () => (
      <Center>
        <p>Loading camera...</p>
      </Center>
    ),
  }
);
function Signup({
  changeScreenType,
  handleClose,
  type = "tenant",
  setIsNext,
  isNext,
}: changeScreenType) {
  const {
    form: {
      getInputProps,
      key,
      errors,
      setFieldValue,
      values: { image },
    },
    handleSubmit,
    isPending,
    isPhoneError,
    setIsPhoneError,
    inputRef,
    onChange,
    value,
    contextValue: { propertySearchData },
    isPropertySearch,
    setIsModalOpen,
    searchMutate,
    landLordLoading,
    livePhotoError,
    setLivePhotoError,
  } = useSignup({ changeScreenType, handleClose, type, setIsNext, isNext });

  const dispatch = useAppDispatch();
  const { mutate } = useMutation({
    mutationFn: googleLogin,
    onSuccess: (data) => {
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
    onSuccess: (tokenResponse: any) => {
      mutate({
        social_type: "google",
        token: tokenResponse?.access_token,
      });
    },
  });

  const [filename, setFilename] = useState<string>("");
  const [webcamOn, setWebcamOn] = useState<boolean>(true);
  const handleCapture = (imageSrc: string) => {
    if (imageSrc) {
      setFieldValue("image", imageSrc);
      const generatedFilename = `CapturedImage.png`;
      setFilename(generatedFilename);
    } else {
      setFieldValue("image", "");
    }
  };

  return (
    <div className="signup_card_sc">
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
        {isNext ? (
          <LivenessQuickStartReact onCapture={handleCapture} />
        ) : (
          <Box className="inner_hight_fm">
            <TextInput
              label="Name"
              placeholder="Please enter your Name"
              withAsterisk
              {...getInputProps("name")}
            />
            <Box mt={"8px"} className="country_dropdown">
              <CustomApiSelect
                labelKey="name"
                label="Country"
                placeholder="Select Country Name"
                queryFn={getAllCountries}
                {...getInputProps("country")}
                onChange={(value, additionalData) => {
                  getInputProps("country").onChange(additionalData?.label);
                }}
              />
            </Box>
            <TextInput
              label="Email"
              placeholder="Please enter your Email ID"
              withAsterisk
              type="email"
              mt="sm"
              {...getInputProps("email")}
            />

            <label
              style={{
                marginBottom: "5px",
                marginTop: "10px",
                display: "block",
              }}
            >
              Phone
            </label>

            <div className="cuntry_input">
              <input ref={inputRef} id="#phone" style={{ display: "none" }} />
              <input
                id="mobile_code"
                onChange={onChange}
                value={value}
                type="text"
                maxLength={21}
              />
            </div>

            {errors?.phone ? (
              <span className="phone_error">{errors?.phone}</span>
            ) : null}

            <Grid>
              <Grid.Col span={6}>
                <PasswordInput
                  mt="sm"
                  label="Password"
                  placeholder="Create your Password"
                  {...getInputProps("password")}
                  key={key("password")}
                />
              </Grid.Col>
              <Grid.Col span={6}>
                <PasswordInput
                  mt="sm"
                  label="Confirm password"
                  placeholder="Please re-enter your Password"
                  key={key("confirmPassword")}
                  {...getInputProps("confirm_password")}
                />
              </Grid.Col>
            </Grid>

            {/* {type === 'landLord' ? (Z
              <>
                <Grid>
                  <Grid.Col span={6}>
                    <div
                      style={{
                        height: 38,
                        border: '1px solid #ddd',
                        borderRadius: 5,
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        paddingLeft: 8,
                        paddingRight: 5,
                        cursor: 'pointer',
                      }}
                      onClick={(event) => {
                        if (!filename) {
                          setWebcamOn(true);
                        }
                      }}
                    >
                      <CustomText size="13" className="responsive_text">
                        {filename ? filename : 'Take picture'}
                      </CustomText>
                      <Flex align={'center'} gap={'xs'}>
                        {filename ? (
                          <IconX
                            onClick={() => {
                              setFilename('');
                              setFieldValue('image', '');
                            }}
                            size={17}
                            style={{
                              backgroundColor: '#D9D9D9',
                              borderRadius: 20,
                              padding: 2,
                              cursor: 'pointer',
                            }}
                          />
                        ) : null}
                        <IconCamera
                          size={20}
                          color="#000000"
                          stroke={1.5}
                          // onClick={() => {
                          //   setWebcamOn(true);
                          // }}
                        />
                      </Flex>
                    </div>
                  </Grid.Col>
                  <Grid.Col span={6}></Grid.Col>
                </Grid>
                {errors?.image ? (
                  <span className="phone_error">{errors?.image}</span>
                ) : null}
              </>
            ) : null} */}
          </Box>
        )}
        <Group className="foot_form">
          <CustomButton
            disabled={isNext ? !image : false}
            loading={
              type === "landLord"
                ? landLordLoading
                : type === "tenant"
                ? isPending
                : false
            }
            onClick={() => {
              handleSubmit();
              // if (!(value.length >= 9)) {
              //   setIsPhoneError(true);
              // } else {
              //   setIsPhoneError(false);
              // }
            }}
          >
            {type === "landLord" && !isNext ? "Next" : "Sign up"}
          </CustomButton>
        </Group>
      </Box>
    </div>
  );
}

export default Signup;
