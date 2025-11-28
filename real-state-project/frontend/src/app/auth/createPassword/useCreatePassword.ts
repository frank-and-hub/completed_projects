import { setNewPassword } from "@/api/auth/forgotpassword";
import { profileUpdatePassword } from "@/api/auth/profile";
import { useGlobalContext } from "@/utils/context";
import { notification } from "@/utils/notification";
import {
  setPasswordValidationSchema,
  updateProfileOtpValidationSchema,
} from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation, useQueryClient } from "@tanstack/react-query";

const useCreatePassword = ({
  changeScreenType,
  handleClose,
}: changeScreenType) => {
  const queryClient = useQueryClient();

  const { contextValue, setContextValue } = useGlobalContext();

  const { isPending, mutate } = useMutation({
    mutationFn: setNewPassword,
    onSuccess: () => {
      notification({
        message: "Password change successfully.",
      });
      handleClose && handleClose();
    },
  });
  const form = useForm<{
    password: string;
    confirm_password: string;
    oldPassword: string;
  }>({
    initialValues: {
      confirm_password: "",
      password: "",
      oldPassword: "",
    },
    validate: yupResolver(
      contextValue.otpVerificationType === "mobile"
        ? setPasswordValidationSchema
        : updateProfileOtpValidationSchema
    ),
  });
  const { isPending: profileUpdateLoading, mutate: profileUpdate } =
    useMutation({
      mutationFn: profileUpdatePassword,
      onSuccess: async (data) => {
        try {
          setContextValue((prev: contextValuesType) => ({
            ...prev,
            profileThings: {
              ...prev.profileThings,
              verifytype: data?.data?.verifytype,
              type: "password",
              new_password: form?.values?.password,
            },
            otpVerificationType: "email",
            email: data?.data?.email,
            other: "",
          }));
          changeScreenType("verifyOTP");

          // handleClose();
          // await queryClient.invalidateQueries({ queryKey: ['profileDetail'] });
          // notification({
          //   message: 'Profile updated Successfully',
          // });
        } catch (err) {
          console.log({ err });
        }
      },
    });

  const handleNewPassword = form.onSubmit((data) => {
    const { confirm_password, password, oldPassword } = data;
    if (contextValue.otpVerificationType === "mobile") {
      mutate({
        confirm_password,
        password,
        user_id: contextValue.userId,
        verify_token: contextValue?.profileThings?.verifyToken ?? "",
      });
    } else {
      // const {
      //   profileThings: { image, name, phone, verifytype, countryCode },
      // } = contextValue;
      // const formData = new FormData();
      // if (typeof image !== 'string') {
      //   formData.append('image', image, image?.name ?? '');
      // }
      // const length = countryCode?.length;
      // const newPhone = phone.substring(length, phone?.length);
      // formData.append('name', name);
      // formData.append('phone', newPhone);
      // formData.append('verifytype', verifytype);
      // formData.append('confirm_password', confirm_password);
      // formData.append('new_password', password);
      // formData.append('old_password', oldPassword);
      // formData.append(
      //   'country_code',
      //   `${countryCode.includes('+') ? '' : '+'}${countryCode}`
      // );
      profileUpdate({
        confirm_password,
        new_password: password,
        old_password: oldPassword,
      });
    }
  });
  return {
    handleNewPassword,
    isPending,
    form,
    contextValue,
    profileUpdateLoading,
  };
};
export default useCreatePassword;
