"use client";

import { Group, Stack, Title, PasswordInput } from "@mantine/core";
import { createFormContext } from "@mantine/form";
import CustomButton from "@/components/buttons/Button";
import Link from "next/link";
import { validateConfirmPassword, validatePassword } from "@/utils/validation";
import PasswordInputText from "@/components/inputs/passwordInputText";
import { randomId } from "@mantine/hooks";
import { cc } from "@/utils/console";
const [FormProvider, useFormContext, useForm] = createFormContext<FormValues>();

interface FormValues {
  password: string;
  confirmPassword: string;
}

function ContextField() {
  const form = useFormContext();
  return (
    <>
      <PasswordInputText
        name={`password`}
        label={`New Password`}
        type={`password`}
        key={form.key('password')}
        {...form.getInputProps('password')}
      />
      <PasswordInputText
        name={`confirmPassword`}
        label={`Confirm Password`}
        type={`password`}
        key={form.key('confirmPassword')}
        {...form.getInputProps('confirmPassword')}
      />
    </>
  );
}

export default function ResetPasswordPage() {

  const form = useForm({
    mode: 'controlled',
    initialValues: {
      password: '',
      confirmPassword: ''
    },

    validate: {
      password: validatePassword,
      confirmPassword: (value: string): null | string => validateConfirmPassword(form.values.password)(value),
    },
    validateInputOnChange: true,
    onSubmitPreventDefault: 'always',
  });

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const isValid = await form.validate(); // runs validation for all fields

    if (!isValid) {
      console.warn('Form has validation errors.');
      return;
    }
    cc();
  };

  return (
    <>
      <Title order={2} className={`text-center`} mb="md" onClick={() => form.setValues({
        password: randomId(),
        confirmPassword: randomId(),
      })}>
        Reset Password
      </Title>
      <FormProvider form={form}>
        <form onSubmit={(e) => handleSubmit(e)}>
          <Stack>
            <ContextField />
            <Group mt="md" justify="space-between" gap="lg" grow>
              <CustomButton
                href={`/admin/dashboard`}
                type={`submit`}
                name={`Submit New Password`}
                className={`w-full`}
                component={Link}
              />
            </Group>
          </Stack>
        </form>
      </FormProvider>
    </>
  );
}