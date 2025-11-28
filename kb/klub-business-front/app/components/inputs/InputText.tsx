'use client';

import { useFormContext } from '@/context/FormContext';
import { cap } from '@/utils/helpers';
import { inputLabel, inputStyle, radius } from '@/utils/style';
import { TextInput } from '@mantine/core';
import React from 'react';

interface InputTextProps {
  name: string;
  label: string;
  type?: string | `text` | `date` | `email` | `password` | `tel` | `number` | `url` | `datetime-local` | `time`;
  required?: boolean;
  [x: string]: any;
}

const styles = {
  input: {
    outline: 'transparent',
    // borderColor: 'inherit',
    // WebkitBoxShadow: 'none',
    // boxShadow: 'none',
    '&:focus': {
      outline: 'transparent',
      // borderColor: 'inherit',
      // WebkitBoxShadow: 'none',
      // boxShadow: 'none',
    },
  },
};

export default function InputText({
  name,
  label,
  type = 'text',
  required = false,
  ...rest
}: InputTextProps) {
  const { formData, handleChange, errors } = useFormContext();

  return (
    <TextInput
      name={name}
      label={cap(label)}
      type={type}
      required={required}
      value={formData?.[name] || formData?.[label] || ''}
      onChange={handleChange}
      error={errors?.[name]}
      radius={radius}
      styles={styles}
      autoComplete={`off`}
      classNames={{
        label: `${inputLabel}`,
        input: `${inputStyle}`,
      }}
      onPaste={(e) => e.preventDefault()}
      onCopy={(e) => e.preventDefault()}
      {...rest}
    />
  );
}
