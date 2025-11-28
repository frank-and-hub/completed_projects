import { googleLogin, login, outLookLogin } from "@/api/auth/login";
import { get_api } from "@/api/root_apis/root_api";
import { searchFilter } from "@/api/search/searchFilter";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import {
  updatePropertyInformation,
  updatePropertySearch,
  updateToken,
  updateUserInformation,
} from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { loginValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useEffect, useState } from "react";

const useLogin = (
  changeScreenType: changeScreenTypeFunction,
  handleClose?: () => void
) => {
  const queryClient = useQueryClient();
  const [rememberMe, setRememberMe] = useState(false);
  const dispatch = useAppDispatch();
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
          dispatch(updateUserInformation(res));
          handleClose && handleClose();
          if (
            !res?.phone ||
            !res?.user_employment?.emplyee_type ||
            !res?.user_employment?.live_with
          ) {
            setIsModalOpen("authModal");
          } else if (res?.subscription && isPropertySearch) {
            searchMutate({
              ...propertySearchData,
            });
          } else if (!res?.subscription && isPropertySearch) {
            setIsModalOpen("selectPlan");
          }
        });
        // window.location.reload();
      }
    };

    window.addEventListener("storage", handleStorageChange);

    return () => {
      window.removeEventListener("storage", handleStorageChange);
    };
  }, [dispatch]);

  const { setPhone, setContextValue } = useGlobalContext();
  const { mutate, isPending } = useMutation({
    mutationFn: login,
    onSuccess: (data: loginType) => {
      if (rememberMe) {
        localStorage.setItem("email", form?.values?.email);
        localStorage.setItem("password", form?.values?.password);
      } else {
        localStorage.removeItem("username");
        localStorage.removeItem("password");
      }
      changeScreenType("verifyOTP");
      setPhone(data?.data?.phone);
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        otpType: "general",
        otpVerificationType: "mobile",
        profileThings: {
          ...prev.profileThings,
          countryCode: data?.data?.country_code,
        },
      }));
    },
  });

  const form = useForm({
    initialValues: {
      email: "",
      password: "",
    },
    validate: yupResolver(loginValidationSchema),
  });
  useEffect(() => {
    const savedEmail = localStorage.getItem("email");
    const savedPassword = localStorage.getItem("password");
    console.log({ savedEmail, savedPassword });

    if (savedEmail && savedPassword) {
      form.setFieldValue("email", savedEmail);
      form.setFieldValue("password", savedPassword);
      setRememberMe(true);
    }
  }, []);
  const handleSubmit = form.onSubmit((data) => {
    const { email, password } = data;
    mutate({ email, password });
  });

  return {
    form,
    handleSubmit,
    isPending,
    searchMutate,
    setIsModalOpen,
    isPropertySearch,
    contextValue,
    rememberMe,
    setRememberMe,
  };
};
export default useLogin;
