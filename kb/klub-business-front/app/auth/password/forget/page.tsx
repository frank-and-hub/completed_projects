"use client";

import { Group, Stack, Title } from "@mantine/core";
import { useForm } from "@mantine/form";
import Link from "next/link";
import InputText from "@/components/inputs/InputText";
import CustomButton from "@/components/buttons/Button";
import { validateEmail } from "@/utils/validation";
import { randomId } from "@mantine/hooks";
import { cc } from "@/utils/console";

interface ForgetPasswordProps {
  email: string;
}

export default function ForgetPasswordPage() {

  const form = useForm<ForgetPasswordProps>({
    mode: 'controlled',
    initialValues: {
      email: ''
    },

    validate: {
      email: validateEmail,
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
        email: `${randomId()}@test.com`,
      })}>
        Forget Password
      </Title>
      <form onSubmit={(e) => handleSubmit(e)}>
        <Stack>
          <InputText
            label={`Email Address`}
            name={`email`}
            type={`email`}
            key={form.key('email')}
            {...form.getInputProps('email')}
          />
          <Group mt="md" justify="space-between" gap="lg" grow>
            <CustomButton
              href={`/auth/password/reset`}
              type={`button`}
              name={`Send Reset Link`}
              className={`w-full`}
              component={Link}
            />
          </Group>
        </Stack>
      </form>
    </>
  );
}