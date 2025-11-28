import { forgotPassword } from "@/api/auth/forgotpassword";
import { useGlobalContext } from "@/utils/context";
import { forgotPasswordValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation } from "@tanstack/react-query";

const useForgotPassword = (changeScreenType: changeScreenTypeFunction) => {
  const { setContextValue, setPhone } = useGlobalContext();
  const form = useForm({
    initialValues: {
      email: "",
    },
    validate: yupResolver(forgotPasswordValidationSchema),
  });
  const { mutate, isPending } = useMutation({
    mutationFn: forgotPassword,
    onSuccess: (data: loginType) => {
      changeScreenType("verifyOTP");
      setContextValue((prev: contextValuesType) => ({
        ...prev,
        otpType: "forgot",
        userId: data?.data?.id,
        otpVerificationType: "mobile",
        profileThings: {
          ...prev?.profileThings,
          countryCode: data?.data?.country_code,
        },
      }));
      setPhone(data?.data?.phone);
    },
  });
  const forgotPasswordHandler = form.onSubmit((data) => {
    const { email } = data;
    mutate({ email });
  });
  return { isPending, forgotPasswordHandler, form };
};

export default useForgotPassword;
