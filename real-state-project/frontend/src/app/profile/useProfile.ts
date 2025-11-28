import { updateProfile } from "@/api/auth/profile";
import { useAppSelector } from "@/store/hooks";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { updateProfileValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import intlTelInput from "intl-tel-input";
import { ChangeEvent, useEffect, useMemo, useRef, useState } from "react";
import phoneNumberAutoFormat from "./NumberFormat";

interface initialValue extends IObject {
  name: string;
  email: string;
  password: string;
  logo: File | string;
  phone: string;
  country: string;
}
const useProfile = (
  changeScreenType: changeScreenTypeFunction,
  handleClose: any
) => {
  const queryClient = useQueryClient();
  const { userDetail } = useAppSelector((state) => state?.userReducer);
  const { setContextValue, setPhone } = useGlobalContext();

  const [isPhoneError, setIsPhoneError] = useState<boolean>(false);
  const [isDisableInputs, setIsDisableInputs] = useState<boolean>(true);
  const [value, setValue] = useState<string>("");
  const inputRef = useRef<any>(null);
  const [countryCode, setCountryCode] = useState<any>(
    userDetail?.country_code ?? ""
  );
  const [phoneNumber, setPhoneNumber] = useState<string>(
    userDetail?.phone ?? ""
  );

  const { isPending, mutate } = useMutation({
    mutationFn: updateProfile,
    onSuccess: (data: profileUpdateResponseType) => {
      if (data?.data?.type === "verify") {
        const { name, phone, logo } = form.values;
        setContextValue((prev: contextValuesType) => ({
          ...prev,
          otpVerificationType: "email",
          email: data?.data?.email ?? "",
          profileThings: {
            ...prev.profileThings,
            image: logo,
            name: name,
            phone: phoneNumber,
            verifytype: data?.data?.verifytype,
            countryCode: !countryCode.includes("+")
              ? `+${countryCode}`
              : countryCode,
            type: "phone",
            country: form?.values?.country,
            emplyee_type: form?.values?.emplyee_type ?? "",
            live_with: form?.values?.live_with ?? "",
          },
          other: "mobile",
        }));
        setPhone(phoneNumber);
        changeScreenType("verifyOTP");
      } else {
        handleClose();
        queryClient.invalidateQueries({ queryKey: [...profileQueryKey.list] });
        notification({
          message: "Profile updated successfully.",
        });
      }
    },
  });
  const form = useForm<initialValue>({
    initialValues: {
      email: userDetail?.email ?? "",
      password: "",
      name: userDetail?.name ?? "",

      logo: userDetail?.image ?? "",
      phone: userDetail?.phone ?? "",
      country: "",
      emplyee_type: userDetail?.user_employment?.emplyee_type ?? "",
      live_with: userDetail?.user_employment?.live_with ?? "",
    },
    validate: yupResolver(updateProfileValidationSchema),
  });
  const defaultCountryData = useMemo(() => {
    if (userDetail?.country) {
      return {
        label: userDetail?.country ?? "",
        value: userDetail?.country,
        id: userDetail?.country,
      };
    }

    return null;
  }, [userDetail?.country]);
  const handleSubmit = form.onSubmit((data) => {
    const { email, name, password, phone } = data;
    const payload: any = { email, name, phone };
    if (password) {
      payload["password"] = password;
    }
    const formData = new FormData();

    formData.append(
      "country_code",
      `${countryCode.includes("+") ? "" : "+"}${countryCode}`
    );
    for (const key in data) {
      if (key === "logo") {
        typeof data?.logo !== "string" &&
          data?.logo &&
          formData.append("image", data?.logo, data?.logo?.name ?? "");
      } else {
        if (key === "password") {
          if (data[key]) {
            formData.append(key, data?.[key]);
          }
        } else if (key === "phone") {
          formData.append(key, phoneNumber);
        } else {
          formData.append(key, data?.[key]);
        }
      }
    }

    mutate({ data: formData });
  });
  const onSubmit = () => {
    if (isDisableInputs) {
      setIsDisableInputs(false);
    } else {
      handleSubmit();
    }
  };
  const setPasswordHandler = () => {
    changeScreenType("createPassword");
    setContextValue((prev: contextValuesType) => ({
      ...prev,
      otpVerificationType: "email",
    }));
  };

  useEffect(() => {
    if (inputRef.current) {
      const iti = intlTelInput(inputRef.current, {
        initialCountry: "za",
        separateDialCode: true,
        utilsScript:
          "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.4/js/utils.js",
        formatAsYouType: true,
        useFullscreenPopup: false,
      });
      if (countryCode) {
        iti.setNumber(countryCode);
      } else {
        setCountryCode(iti.getSelectedCountryData()?.dialCode);
      }
      inputRef.current.addEventListener("countrychange", () => {
        setCountryCode(iti.getSelectedCountryData()?.dialCode);
      });
      return () => {
        iti.destroy();
      };
    }
  }, [isDisableInputs]);

  useEffect(() => {
    if (userDetail?.phone) {
      const targetValue = phoneNumberAutoFormat(userDetail?.phone);
      setValue(targetValue);
    }
  }, []);

  const onChange = (e: ChangeEvent<HTMLInputElement>) => {
    const temp = e.target.value.trim().replace(/[^0-9]/g, "");

    if (temp[0] === "0") {
      const newNum = temp.slice(1, temp.length);
      setPhoneNumber(newNum);
      const targetValue = phoneNumberAutoFormat(newNum);
      setValue(targetValue);
    } else {
      setPhoneNumber(temp);
      const targetValue = phoneNumberAutoFormat(temp);
      setValue(targetValue);
    }
  };

  return {
    userDetail,
    form,
    handleSubmit,
    isPending,
    isDisableInputs,
    onSubmit,
    isPhoneError,
    setIsPhoneError,
    setIsDisableInputs,
    setPasswordHandler,
    onChange,
    inputRef,
    value,
    defaultCountryData,
  };
};

export default useProfile;
