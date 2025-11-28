import { landlordOtpVerify, landLordResendOtp } from "@/api/auth/landlord";
import { profileVerify } from "@/api/auth/profile";
import { otpVerify } from "@/api/auth/register";
import { authResendOtp, profileResendOtp } from "@/api/auth/resendOtp";
import { searchFilter } from "@/api/search/searchFilter";
import { useAppDispatch, useAppSelector } from "@/store/hooks";
import { updateToken } from "@/store/reducer/userReducer";
import { useGlobalContext } from "@/utils/context";
import { getBaseURl } from "@/utils/createIconUrl";
import { notification } from "@/utils/notification";
import { profileQueryKey } from "@/utils/queryKeys/profileQueryKey";
import { otpValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";

const useVerificationFailed = ({
  changeScreenType,
  handleClose,
  isClose,
}: {
  changeScreenType: changeScreenTypeFunction;
  handleClose: any;
  isClose: boolean;
}) => {
  const queryClient = useQueryClient();

  const { phone, setIsModalOpen, contextValue, setContextValue } =
    useGlobalContext();
  const {
    propertySearchData: propertySearchFromContext,
    otpVerificationType,
    other,
    email,
    isSearchApiCall,
    profileThings: { countryCode, verifytype, phone: otpPhone },
  } = contextValue;
  const [isResend, setIsResend] = useState<boolean>(false);
  const dispatch = useAppDispatch();
  const { userDetail, propertySearchData, isPropertySearch } = useAppSelector(
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
        notification({
          message: "Your request has been submitted.",
        });

        setIsModalOpen("thankYou");
        handleClose && handleClose();
      } catch (err) {
        console.error(err);
      }
    },
  });
  const { mutate, isPending } = useMutation({
    mutationFn: otpVerify,
    onSuccess: (data: userDetailElementType) => {
      if (!data?.data?.user?.subscription && isSearchApiCall) {
        setIsModalOpen("selectPlan");
      } else if (isSearchApiCall) {
        searchMutate({
          ...propertySearchFromContext,
        });
        window.dispatchEvent(new Event("new-event"));
      }
      if (!isSearchApiCall) {
        contextValue.otpType === "general"
          ? handleClose()
          : contextValue?.otpType === "register"
          ? null
          : changeScreenType("createPassword");
      }
      (contextValue.otpType === "general" ||
        contextValue.otpType === "register") &&
        dispatch(updateToken(data.data));
      if (!isSearchApiCall) {
        (contextValue.otpType === "general" ||
          contextValue.otpType === "register") &&
          handleClose();
      }
      contextValue.otpType === "forgot" &&
        setContextValue((prev: contextValuesType) => ({
          ...prev,
          profileThings: {
            ...prev.profileThings,
            verifyToken: data?.data?.verify_token,
          },
        }));
    },
    onError: () => {
      changeScreenType("verificationFailed");
    },
  });
  const { mutate: landlordMutation, isPending: landlordLoading } = useMutation({
    mutationFn: landlordOtpVerify,
    onSuccess: (res) => {
      if (res.data?.email) {
        setContextValue((prev: contextValuesType) => ({
          ...prev,
          otpVerificationType: "email",
          isLandLord: true,
          email: res.data?.email,
        }));
        form.setFieldValue("otp", "");
      } else {
        setContextValue((prev: contextValuesType) => ({
          ...prev,
          isLandLord: false,
        }));
        notification({
          message: "Landlord registered successfully.",
        });
        window.location.href = getBaseURl() + "/privatelandlord/login";
        handleClose();
      }
    },
  });
  const { mutate: profileOtpVerify, isPending: profileOtpLoading } =
    useMutation({
      mutationFn: profileVerify,
      onSuccess: (data: userDetailElementType) => {
        handleClose();
        queryClient.invalidateQueries({ queryKey: [...profileQueryKey.list] });

        notification({
          // title: 'Updating',
          message: "Profile updated successfully.",
        });
        if (!isClose && userDetail?.subscription && isPropertySearch) {
          searchMutate({
            ...propertySearchData,
          });
        } else if (!isClose && !userDetail?.subscription && isPropertySearch) {
          setIsModalOpen("selectPlan");
        }
      },
      onError: () => {
        changeScreenType("verificationFailed");
      },
    });
  const form = useForm<{ otp: string }>({
    initialValues: {
      otp: "",
    },
    validate: yupResolver(otpValidationSchema),
  });
  const handleSubmit = form.onSubmit((data) => {
    const { otp } = data;
    if (contextValue?.isLandLord) {
      otpVerificationType === "email"
        ? landlordMutation({ otp, verifytype: "email", email })
        : landlordMutation({ otp, verifytype: "phone", phone });
      return;
    }
    if (contextValue.otpVerificationType === "mobile") {
      mutate({
        otp,
        phone: phone,
        verifytype:
          contextValue.otpType === "general" ||
          contextValue?.otpType === "register"
            ? "general"
            : "forgot",
      });
    } else {
      const {
        profileThings: {
          name,
          phone,
          verifytype,
          countryCode,
          type,
          new_password,
          image,
        },
      } = contextValue;
      const formData = new FormData();
      if (typeof image !== "string") {
        formData.append("image", image, image?.name ?? "");
      }
      if (type === "password") {
        formData.append("new_password", new_password ?? "");
      } else {
        formData.append("name", name);
        formData.append("phone", phone);
        formData.append(
          "country_code",
          `${countryCode.includes("+") ? "" : "+"}${countryCode}`
        );
      }

      formData.append("verifytype", verifytype);
      formData.append("otp", otp);

      profileOtpVerify({ data: formData });
    }
  });
  const { isPending: resendLoading, mutate: loginResendOtp } = useMutation({
    mutationFn: authResendOtp,
  });
  const { isPending: profileResendLoading, mutate: profileUpdateResendOtp } =
    useMutation({
      mutationFn: profileResendOtp,
    });
  const {
    isPending: landLordResendOtpLoading,
    mutate: landLordResendOtpMutate,
  } = useMutation({ mutationFn: landLordResendOtp });
  const resendOtp = () => {
    if (contextValue?.isLandLord) {
      otpVerificationType === "email"
        ? landLordResendOtpMutate({ verifytype: "email", email })
        : landLordResendOtpMutate({ verifytype: "phone", phone });
      return;
    }
    if (otpVerificationType === "mobile") {
      loginResendOtp({ phone });
    } else {
      profileUpdateResendOtp({
        country_code: `${countryCode.includes("+") ? "" : "+"}${countryCode}`,
        phone: otpPhone,
        verifytype: verifytype,
      });
    }
  };
  return {
    form,
    handleSubmit,
    isPending,
    phone,
    contextValue,
    profileOtpLoading,
    isResend,
    setIsResend,
    resendOtp,
    resendLoading,
    profileResendLoading,
    other,
    landLordResendOtpLoading,
    landlordLoading,
  };
};
export default useVerificationFailed;
