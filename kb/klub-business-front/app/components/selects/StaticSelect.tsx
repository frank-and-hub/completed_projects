'use client';

import React from 'react';
import { Select } from '@mantine/core';
import { useFormContext } from '@/context/FormContext';
import { cap } from '@/utils/helpers';
import { StaticOptions } from './staticSelectOptions';
import { inputLabel, radius, selectStyle } from '@/utils/style';
import { StaticSelectProps } from '@/types/CommonQueryParams';

export default function StaticSelect({
    name,
    label,
    required = false,
    ...rest
}: StaticSelectProps) {
    const options = StaticOptions(name);
    const { formData, handleChange, errors } = useFormContext();

    return (
        <div className={`mb-4`}>
            <Select
                label={
                    label && required ? (
                        <>
                            {cap(label)} <span className={`text-red-500`}>*</span>
                        </>
                    ) : (
                        label ? cap(label) : ''
                    )
                }
                data={[{ value: '', label: 'Select an option' }, ...options]}
                value={formData[name]?.toString() || ''}
                onChange={(value) => handleChange({ target: { name, value } })}
                error={errors?.[name]}
                name={name}
                size={`sm`}
                radius={radius}
                allowDeselect
                checkIconPosition={`right`}
                withScrollArea
                comboboxProps={{
                    middlewares: { flip: false, shift: false, inline: false },
                    transitionProps: { transition: 'pop', duration: 200 },
                    shadow: 'md'
                }}
                classNames={{
                    input: `${selectStyle}`,
                    dropdown: 'bg-white max-h-60',
                    label: `${inputLabel}`,
                    option: 'hover:bg-gray-100',
                    options: 'max-h-60 overflow-y-auto',
                }}
                onPaste={(e) => e.preventDefault()}
                onCopy={(e) => e.preventDefault()}
                {...rest}
            />
        </div>
    );
}
