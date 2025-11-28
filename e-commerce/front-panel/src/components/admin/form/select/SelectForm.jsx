import React from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'

function SelectForm({ id, handleChange, value, error, required = false, disabled = false, label = null, Options, isMulti = false }) {
    const optionsWithPlaceholder = !isMulti
        ? [
            {
                value: '',
                label: `Select ${ucwords(id.replace('_id', ''))}`,
                isDisabled: true
            },
            ...Options
        ]
        : Options;

    var handleSelectChange, selectedValueOptions;

    if (isMulti) {
        handleSelectChange = (selectedOptions) => {
            const selectedValues = selectedOptions
                ? selectedOptions.map(option => option.value)
                : [];
            handleChange({ target: { name: id, value: selectedValues } });
        };
        selectedValueOptions = optionsWithPlaceholder?.filter(option => value.includes(option.value));
    } else {
        handleSelectChange = (selectedOption) => {
            handleChange({ target: { name: id, value: selectedOption?.value } });
        };
        selectedValueOptions = optionsWithPlaceholder?.find(option => option.value === value) || null;
    }

    return (
        <>
            {label ? (
                <label htmlFor={id} className={`form-label text-capitalize`}>{(label)} {required ? (<span className='text-danger'>*</span>) : ('')}</label>
            ) : ('')}
            <Select
                className={error ? `is-invalid` : ``}
                id={id}
                options={optionsWithPlaceholder}
                value={selectedValueOptions}
                placeholder={`Select ${ucwords(label)}`}
                onChange={handleSelectChange}
                styles={customStyles}
                isDisabled={disabled}
                isMulti={isMulti}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectForm