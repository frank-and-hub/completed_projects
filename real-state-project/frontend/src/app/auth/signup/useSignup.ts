import { signupAsLandlord } from "@/api/auth/landlord";
import { register } from "@/api/auth/register";
import { get_api } from "@/api/root_apis/root_api";
import { searchFilter } from "@/api/search/searchFilter";
import phoneNumberAutoFormat from "@/app/profile/NumberFormat";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
  updateToken,
  updateUserInformation,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import __DEV__ from "@/utils/devCheck";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import {
  landlordRegisterValidationSchema,
  registerValidationSchema,
} from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import intlTelInput from "intl-tel-input";
import { ChangeEvent, useEffect, useRef, useState } from "react";

const useSignup = ({
  changeScreenType,
  handleClose,
  type,
  setIsNext,
  isNext,
}: changeScreenType) => {
  const dispatch = useAppDispatch();
  const [livePhotoError, setLivePhotoError] = useState<boolean>(false);

  const queryClient = useQueryClient();
  const { setPhone, setContextValue } = useGlobalContext();
  const [isPhoneError, setIsPhoneError] = useState<boolean>(false);
  const [value, setValue] = useState<string>("");
  const inputRef = useRef<any>(null);
  const [countryCode, setCountryCode] = useState<any>();
  const { mutate, isPending } = useMutation({
    mutationFn: register,
    onSuccess: (data, payload) => {
      const { phone, country_code } = payload;
      changeScreenType("verifyOTP");
      setPhone(phone);
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        otpType: "register",
        otpVerificationType: "mobile",
        profileThings: {
          ...prev.profileThings,
          countryCode: country_code,
        },
      }));
    },
    onError: () => {},
  });
  const { mutate: landLordMutate, isPending: landLordLoading } = useMutation({
    mutationFn: signupAsLandlord,
    onSuccess: (data, payload: any) => {
      const fileValue = payload?.data?.get("phone");

      setPhone(fileValue);
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        otpVerificationType: "mobile",
        isLandLord: type === "landLord",
      }));
      setIsNext && setIsNext(false);
      changeScreenType("verifyOTP");
    },
    onError: () => {},
  });
  const { contextValue, setIsModalOpen } = useGlobalContext();
  const { isPropertySearch, propertySearchData, userDetail } = useAppSelector(
    (state) => state?.userReducer
  );
  const { mutate: searchMutate, isPending: searchLoading } = useMutation({
    mutationFn: searchFilter,
    onSuccess: async (data) => {
      try {
        if (data?.total_request === 5) {
          await queryClient.invalidateQueries({
            queryKey: [...profileQueryKey.list],
          });
        }
        dispatch(updatePropertyInformation(undefined));
        dispatch(updatePropertySearch(false));
        notification({
          message: "Your request has been submitted.",
        });
        setIsModalOpen("thankYou");
        // handleClose && handleClose();
      } catch (err) {
        console.error(err);
      }
    },
  });
  useEffect(() => {
    const handleStorageChange = (event: any) => {
      if (event.key === "login-microsoft") {
        get_api(`profile`, {
          headers: {
            Authorization: `Bearer ${event?.newValue}`,
          },
        }).then((res: ProfileDetailType) => {
          dispatch(
            updateToken({
              token: event?.newValue!,
              user: res,
              verify_token: "",
            })
          );
          if (!res?.phone) {
            setIsModalOpen("authModal");
          } else if (res?.subscription && isPropertySearch) {
            searchMutate({
              ...propertySearchData,
            });
          } else if (!res?.subscription && isPropertySearch) {
            setIsModalOpen("selectPlan");
          }
          dispatch(updateUserInformation(res));
          handleClose && handleClose();
        });
      }
    };

    window.addEventListener("storage", handleStorageChange);

    return () => {
      window.removeEventListener("storage", handleStorageChange);
    };
  }, [dispatch]);

  const form = useForm<{
    name: string;
    email: string;
    password: string;
    confirm_password: string;
    phone: string;
    image?: any;
    country: string;
  }>({
    initialValues: {
      name: __DEV__ ? "John Doe" : "",
      email: __DEV__ ? "jhon@gmail.com" : "",
      password: __DEV__ ? "1234678" : "",
      confirm_password: __DEV__ ? "1234678" : "",
      phone: __DEV__ ? "1234567890" : "",
      country: __DEV__ ? "South Africa" : "",
    },
    validate: yupResolver(
      type === "tenant"
        ? registerValidationSchema
        : landlordRegisterValidationSchema
    ),
  });

  const handleSubmit = form.onSubmit((data) => {
    const { confirm_password, email, name, password, phone, image, country } =
      data;

    if (type === "landLord") {
      if (!isNext) {
        setIsNext && setIsNext(true);
      } else {
        const formData = new FormData();
        formData?.append("name", name);
        formData?.append("email", email);
        formData?.append("phone", phone);
        formData?.append("password", password);
        formData?.append("confirm_password", confirm_password);
        formData?.append("country_code", `+${countryCode}`);
        formData?.append("image", image);
        formData?.append("country", country);
        landLordMutate({ data: formData });
      }
    } else if (type === "tenant") {
      mutate({
        confirm_password,
        email,
        name,
        password,
        phone,
        country_code: `+${countryCode}`,
        country,
      });
    }
  });

  const onChange = (e: ChangeEvent<HTMLInputElement>) => {
    const temp = e.target.value.trim().replace(/[^0-9]/g, "");

    if (temp[0] === "0") {
      const newNum = temp.slice(1, temp.length);
      form.setFieldValue("phone", newNum);
      const targetValue = phoneNumberAutoFormat(newNum);
      setValue(targetValue);
    } else {
      form.setFieldValue("phone", temp);

      const targetValue = phoneNumberAutoFormat(temp);
      setValue(targetValue);
    }
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
      setCountryCode(iti.getSelectedCountryData()?.dialCode);
      inputRef.current.addEventListener("countrychange", () => {
        setCountryCode(iti.getSelectedCountryData()?.dialCode);
      });
      return () => {
        iti.destroy();
      };
    }
  }, []);

  return {
    form,
    handleSubmit,
    isPending,
    isPhoneError,
    setIsPhoneError,
    onChange,
    value,
    inputRef,
    contextValue,
    setIsModalOpen,
    isPropertySearch,
    searchMutate,
    landLordLoading,
    livePhotoError,
    setLivePhotoError,
  };
};
export default useSignup;
