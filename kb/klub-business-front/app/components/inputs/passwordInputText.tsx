import { useFormContext } from '@/context/FormContext';
import { cap } from '@/utils/helpers';
import { inputLabel, inputStyle, radius } from '@/utils/style';
import { PasswordInput } from '@mantine/core'
import React from 'react'

interface PasswordInputProps {
    name: string;
    label: string;
    placeholder?: string;
    type: string | 'password';
    withAsterisk?: boolean;
    [x: string]: any;
}

const styles = {
    input: {
        outline: 'transparent',
        // borderColor: 'inherit',
        // 'WebkitBoxShadow': 'none',
        // 'boxShadow': 'none',
        '&:focus': {
            outline: 'transparent',
            // borderColor: 'inherit',
            // 'WebkitBoxShadow': 'none',
            // 'boxShadow': 'none',
        },
    },
};

export default function PasswordInputText({ name, label, placeholder, type, withAsterisk, ...prop }: PasswordInputProps) {
    const { formData, handleChange, errors } = useFormContext();
    return (
        <>
            <PasswordInput
                withAsterisk={withAsterisk}
                label={cap(label)}
                placeholder={placeholder}
                type={type}
                radius={radius}
                classNames={{
                    label: `${inputLabel}`,
                    input: `${inputStyle}`,
                }}
                value={formData?.[name] || ''}
                onChange={handleChange}
                error={errors?.[name]}
                onPaste={(e) => e.preventDefault()}
                onCopy={(e) => e.preventDefault()}
                {...prop}
            />
        </>
    )
}
