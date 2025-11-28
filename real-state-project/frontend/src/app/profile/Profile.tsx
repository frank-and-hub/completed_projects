import CustomButton from "@/components/customButton/CustomButton";
import {
  Avatar,
  Box,
  Button,
  Center,
  FileButton,
  Flex,
  Grid,
  Group,
  NumberInput,
  PasswordInput,
  Select,
  TextInput,
  Title,
} from "@mantine/core";
import { IconPencilMinus, IconTie, IconUsersGroup } from "@tabler/icons-react";
import "intl-tel-input/build/css/intlTelInput.css";
import "react-phone-input-2/lib/style.css";
import ModalCloseIcon from "../home/components/modalCloseIcon/ModalCloseIcon";
import "./profile.scss";
import useProfile from "./useProfile";
import CustomApiSelect from "@/components/customApiSelect/CustomApiSelect";
import { getAllCountries } from "@/api/propertySearchHistory/propertySearch";
import CustomText from "@/components/customText/CustomText";

function Profile({
  changeScreenType,
  handleClose,
  isClose = true,
}: changeScreenType) {
  const {
    form: {
      getInputProps,
      values: { logo },
    },
    isDisableInputs,
    isPhoneError,
    setIsPhoneError,
    setIsDisableInputs,
    handleSubmit,
    setPasswordHandler,
    isPending,
    inputRef,
    onChange,
    value,
    userDetail,
    defaultCountryData,
  } = useProfile(changeScreenType, handleClose);

  const employmentIcon = (
    <Center style={{ width: "1.5rem", height: "1.5rem" }}>
      <IconTie style={{ width: "1.2rem", height: "1.2rem" ,color:"#f30051"}} />
    </Center>
  );
  
  const residentsIcon = (
    <Center style={{ width: "1.5rem", height: "1.5rem" }}>
      <IconUsersGroup style={{ width: "1.2rem", height: "1.2rem", color: "#f30051" }} />
    </Center>
  );

  return (
    <div className="profile_filter_modal">
      {isClose ? (
        <div className="modal_head_close">
          <ModalCloseIcon
            handleClose={() => {
              if (isClose) {
                handleClose && handleClose();
              }
            }}
          />
        </div>
      ) : null}

      <div className="profile_filter_form">
        <Flex direction={"column"}>
          <Box className="profile_card">
            <div className="user_profile">
              <Avatar
                color="cyan"
                radius="xl"
                src={
                  logo
                    ? typeof logo === "string"
                      ? logo
                      : URL.createObjectURL(logo)
                    : null
                }
              />

              <FileButton
                // disabled={isDisableInputs}
                accept="image/png,image/jpeg"
                {...getInputProps("logo")}
              >
                {(props) => (
                  <Button
                    {...props}
                    style={{
                      borderRadius: "50%",
                      padding: 0,
                      width: "2.05rem",
                      minWidth: "2.05rem",
                      height: "2.05rem",
                      position: "absolute",
                      bottom: "-5px",
                      right: "20px",
                    }}
                  >
                    <IconPencilMinus style={{ width: "18px" }} />
                  </Button>
                )}
              </FileButton>
            </div>
            <h2>Profile</h2>
          </Box>
          <Title
            order={5}
            ta={"center"}
            size="xs"
            mt={"-10px"}
            mb="lg"
            c={"#f30051"}
          >
            **Having a profile picture might increase your credibility.
          </Title>
        </Flex>

        <Grid>
          <Grid.Col span={6}>
            <TextInput
              // disabled={isDisableInputs}
              label="Name"
              placeholder="Please enter name"
              {...getInputProps("name")}
            />
          </Grid.Col>
          <Grid.Col span={6}>
            <TextInput
              disabled
              label="Email ID"
              placeholder="Please enter email address"
              {...getInputProps("email")}
            />
          </Grid.Col>
          <Grid.Col span={6}>
            <label
              className="mantine-InputWrapper-label mantine-PasswordInput-label"
              style={{
                marginTop: "3px",
                display: "block",
              }}
            >
              Phone No.
            </label>

            <div className="cuntry_input">
              <input
                ref={inputRef}
                id="#phone"
                style={{ display: "none" }}
                // disabled={isDisableInputs ? true : !!userDetail?.country_code}
              />
              <input
                // ref={inputRef}
                id="mobile_code"
                onChange={onChange}
                value={value}
                type="text"
                maxLength={21}
                // disabled={isDisableInputs}
              />
            </div>
            {isPhoneError ? (
              <span className="phone_error">Mobile number is Required</span>
            ) : null}
          </Grid.Col>

          <Grid.Col span={6}>
            <Box className="country_dropdown">
              <CustomApiSelect
                // isDisabled={isDisableInputs || !!defaultCountryData}
                externalValue={defaultCountryData}
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
          </Grid.Col>

          <Grid.Col span={6}>
            <Select
              label="Employment Status"
              placeholder="Please select your employment status"
              leftSectionPointerEvents="none"
              leftSection={employmentIcon}
              data={[
                { value: "employed", label: "Employed" },
                { value: "contract", label: "Contract" },
                { value: "self_employed", label: "Self-Employed" },
                { value: "student", label: "Student" },
                { value: "retired", label: "Retired" },
                { value: "unemployed", label: "Unemployed" },
              ]}
              searchable
              nothingFoundMessage="Nothing found..."
              comboboxProps={{ transitionProps: { transition: 'pop', duration: 200 }, shadow: 'md'  }}
              {...getInputProps("emplyee_type")}
            />
          </Grid.Col>

          <Grid.Col span={6}>
            <NumberInput
              leftSection={residentsIcon}
              label="Number of Residents"
              placeholder="Please enter number of people"
              {...getInputProps("live_with")}
              min={1}
              max={20}
              clampBehavior="strict"
              allowNegative={false}
              allowDecimal={false}
            />
          </Grid.Col>

          {!["google", "microsoft"].includes(userDetail?.login_type) ? (
            <Grid.Col span={6}>
              <input
                type="email"
                style={{
                  position: "absolute",
                  top: "1000%",
                }}
              />

              <PasswordInput
                // disabled={isDisableInputs}
                label="Password"
                placeholder="*************"
                autoComplete="new-password"
                {...getInputProps("password")}
              />

              <span
                className="set_password"
                style={{ textAlign: "left" }}
                onClick={() => {
                  setPasswordHandler();
                }}
              >
                Change Password
              </span>
            </Grid.Col>
          ) : null}
        </Grid>
        <Group justify="center" mt="xl">
          <CustomButton
            loading={isPending}
            onClick={() => {
              if (!(value.length >= 9)) {
                setIsPhoneError(true);
              } else {
                setIsPhoneError(false);
                handleSubmit();
              }
            }}
          >
            {"Save"}
          </CustomButton>
        </Group>
      </div>
    </div>
  );
}

export default Profile;
