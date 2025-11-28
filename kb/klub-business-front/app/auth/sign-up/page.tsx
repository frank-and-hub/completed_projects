"use client";

import { Checkbox, Group, Stack, Title } from "@mantine/core";
import { useForm } from "@mantine/form";
import Link from "next/link";
import InputText from "@/components/inputs/InputText";
import CustomButton from "@/components/buttons/Button";
import { validateAgreeTerm, validateConfirmPassword, validateEmail, validatePassword } from "@/utils/validation";
import PasswordInputText from "@/components/inputs/passwordInputText";
import { randomId } from "@mantine/hooks";
import { post } from "@/utils/axios";
import { login } from "@/utils/useAuth";
import { useRouter } from "next/navigation";
import { ce } from "@/utils/console";
import CommonSelect from "@/components/selects/CommonSelect";

interface FormValues { name: string; email: string; dialCode: string; phone: number; password: string; confirmPassword: string; aggreTerm: boolean; }

export default function SignUp() {
    const router = useRouter();
    const form = useForm<FormValues>({
        mode: 'controlled',
        initialValues: {
            name: '',
            email: '',
            dialCode: '',
            phone: 0,
            password: '',
            confirmPassword: '',
            aggreTerm: true
        },

        validate: {
            name: (value: string) => { if (!value) return 'Name is required'; return null; },
            email: validateEmail,
            dialCode: (value: string) => { if (!value) return 'Dial code is required'; return null; },
            phone: (value: number) => { if(typeof value !== 'number') return 'Phone number must be a number'; if (!value) return 'Phone number is required'; return null; },
            password: validatePassword,
            confirmPassword: (value: string): null | string => validateConfirmPassword(form.values.password)(value),
            aggreTerm: validateAgreeTerm,
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

        try {
            const res = await post('v1/auth/sign-up', form.values);
            if (res) {
                login(res.access_token);
                router.push('/admin/dashboard');
            }
        } catch (e) {
            ce(e);
        }
    };

    return (
        <>
            <Title order={2} className={`text-center`} mb={`md`} onClick={() => form.setValues({ email: `frank@yopmail.com`, password: 'Klub@1234', confirmPassword: 'Klub@1234', name: 'frank' })}> Sign up your account</Title>

            <form onSubmit={(e) => handleSubmit(e)}>
                <Stack>
                    <InputText label={`Name`} type={`name`} name={`name`} key={form.key('name')} {...form.getInputProps('name')} />
                    <InputText label={`Email Address`} type={`email`} name={`email`} key={form.key('email')} {...form.getInputProps('email')} />
                    <CommonSelect id={`dialCode`} label={`Dial Code`} apiUrl={`/v1/common-data?type=dialCode`} {...form.getInputProps('dialCode')} />
                    <InputText label={`Phone Number`} type={`number`} name={`phone`} key={form.key('phone')} {...form.getInputProps('phone')} />
                    <PasswordInputText name={`password`} label={`Password`} type={`password`} key={form.key('password')}     {...form.getInputProps('password')} />
                    <PasswordInputText name={`confirmPassword`} label={`Confirm Password`} type={`password`} key={form.key('confirmPassword')} {...form.getInputProps('confirmPassword')} />
                    <Checkbox label={`I agree`} variant={`outline`} size={`xs`} description={
                        <>by signing up you agree to our{' '}
                            <Link href={`/terms-and-conditions`} style={{ color: '#007bff', textDecoration: 'underline' }}>terms and conditions</Link>{' '}and{' '}
                            <Link href={`/privacy-policy`} style={{ color: '#007bff', textDecoration: 'underline' }}>privacy policy</Link>.
                        </>
                    } key={form.key('aggreTerm')} {...form.getInputProps('aggreTerm')} defaultChecked />
                    <Group mt={`md`} justify={`space-between`} gap={`lg`} grow>
                        <CustomButton name={`Sign up`} className={`w-full`} type={`submit`} />
                    </Group>
                    <Group mt={`sm`} justify={`center`} gap={`lg`}>
                        <Link href={`/auth/sign-in`} className={`text-xs`} title={`Already have an account ?`}>Sign In</Link>
                    </Group>
                </Stack>
            </form>
        </>
    );
}