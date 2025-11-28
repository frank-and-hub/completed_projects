"use client";

import { Checkbox, Group, PasswordInput, Stack, Title } from '@mantine/core';
import { useForm } from '@mantine/form';
import Link from 'next/link';
import InputText from '@/components/inputs/InputText';
import CustomButton from '@/components/buttons/Button';
import { validateEmail, validatePassword } from '@/utils/validation';
import PasswordInputText from '@/components/inputs/passwordInputText';
import { randomId } from '@mantine/hooks';
import { post } from '@/utils/axios';
import { login, setToken } from '@/utils/useAuth';
import { useRouter } from 'next/navigation';
import { useDispatch } from 'react-redux';
import { ce, cw } from '@/utils/console';

interface FormValues { email: string; password: string; rememberMe: boolean; }

export default function LogInPage() {
    const dispatch = useDispatch();
    const router = useRouter();
    const form = useForm<FormValues>({
        mode: 'controlled',
        initialValues: {
            email: '',
            password: '',
            rememberMe: false,
        },

        validate: {
            email: validateEmail,
            password: validatePassword,
        },

        validateInputOnChange: true,
        onSubmitPreventDefault: 'always',
    });

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const isValid = await form.validate(); // runs validation for all fields

        if (!isValid) {
            cw('Form has validation errors.');
            return;
        }

        try {
            const res = await post('v1/auth/sign-in', form.values);
            if (res) {
                login(res.access_token);
                dispatch(setToken(res.access_token));
                router.push('/admin/dashboard');
            }
        } catch (e) {
            ce(e);
        }
    };

    return (
        <>
            <Title order={2} className={`text-center`} mb="md" onClick={() => form.setValues({
                // email: `${randomId()}@test.com`,
                email: `frank@yopmail.com`,
                // password: randomId(),
                password: 'Klub@1234',
            })}>
                Sign in your account
            </Title>
            <form onSubmit={(e) => handleSubmit(e)}>
                <Stack>
                    <InputText label={`Email Address`} name={`email`} type={`email`} key={form.key('email')}      {...form.getInputProps('email')} />
                    <PasswordInputText name={`password`} label={`Password`} type={`password`} key={form.key('password')}     {...form.getInputProps('password')} />
                    <Group mt="sm" justify={`space-between`} gap={`lg`} grow>
                        <Checkbox label={`Remember me`} variant={`outline`} size={`xs`} key={form.key('rememberMe')}    {...form.getInputProps('rememberMe')} />
                    </Group>
                    <Group mt={`sm`} justify={`space-between`} gap={`lg`} grow>
                        <CustomButton type={`submit`} name={`Sign in`} className={`w-full`} />
                    </Group>
                    <Group mt={`sm`} justify={`space-between`} gap={`lg`}>
                        <Link href='/auth/password/forget' className={`text-xs`} >Forgot password ? </Link>
                        <Link href='/auth/sign-up' className={`text-xs`} title={`Don't have an account ?`}>Sign Up </Link>
                    </Group>
                </Stack>
            </form>
        </>
    );
};