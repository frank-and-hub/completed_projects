"use client";
import { contactUs } from "@/api/auth/contactUs";
import { useGlobalContext } from "@/utils/context";
import { contactUsValidationSchema } from "@/utils/validationSchema";
import { useForm, yupResolver } from "@mantine/form";
import { useMutation } from "@tanstack/react-query";
import { useRef, useState } from "react";

const useContactUs = () => {
  const { setIsModalOpen, isModalOpen } = useGlobalContext();
  const captchaRef = useRef<any>(null);

  const [message, setMessage] = useState<string>("");
  const { isPending, mutate } = useMutation({
    mutationFn: contactUs,
    onSuccess: (res) => {
      form.reset();
      setMessage(
        "Thank you for contacting us! Your request has been successfully submitted. We will get back to you as soon as possible"
      );
      setTimeout(() => {
        setMessage("");
      }, 3000);
    },
  });
  const form = useForm<{
    name: string;
    email: string;
    subject: string;
    message: string;
  }>({
    initialValues: {
      email: "",
      message: "",
      name: "",
      subject: "",
    },
    validate: yupResolver(contactUsValidationSchema),
  });
  const handleSubmit = form.onSubmit((data) => {
    const { email, message, name, subject } = data;
    const tokenValue = captchaRef.current.getValue();
    mutate({ email, message, name, subject, re_captcha: tokenValue });
    captchaRef.current.reset();
  });
  return { form, handleSubmit, isPending, message, captchaRef };
};

export default useContactUs;
