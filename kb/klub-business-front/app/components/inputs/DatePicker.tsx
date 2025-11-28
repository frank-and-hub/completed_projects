
'use client';

import { useFormContext } from '@/context/FormContext';
import { cap } from '@/utils/helpers';
import { inputLabel, inputStyle, radius } from '@/utils/style';
import { DateTimePicker } from '@mantine/dates';
import React from 'react';

interface InputTextProps {
  name: string;
  label: string;
  required?: boolean;
  [x: string]: any;
}

const styles = {
  input: {
    outline: 'transparent',
    '&:focus': {
      outline: 'transparent',
    },
  },
};

export default function DatePicker({
  name,
  label,
  required = false,
  ...rest
}: InputTextProps) {
  const { formData, handleChange, errors } = useFormContext();

  return (
    <DateTimePicker
      name={name}
      label={cap(label)}
      required={required}
      value={formData?.[name] || ''}
      onChange={(value) => handleChange({ target: { name, value } })}
      error={errors?.[name]}
      radius={radius}
      styles={styles}
      withSeconds
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
