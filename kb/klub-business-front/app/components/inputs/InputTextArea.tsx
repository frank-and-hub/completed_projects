'use client';

import { useFormContext } from '@/context/FormContext';
import { cap } from '@/utils/helpers';
import { inputLabel, inputStyle, radius } from '@/utils/style';
import { Textarea, TextInput } from '@mantine/core';
import React from 'react';

interface InputTextAreaProps {
  name: string;
  label: string;
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

export default function InputTextArea({
  name,
  label,
  required = false,
  ...rest
}: InputTextAreaProps) {
  const { formData, handleChange, errors } = useFormContext();

  return (
    <Textarea
      name={name}
      label={cap(label)}
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
