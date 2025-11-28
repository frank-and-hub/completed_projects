import React, { useState, useEffect } from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'

function SelectDialCode({ id, handleChange, value, error, label = null, required = false, disabled = false, multiple = false }) {
    const [response, setResponse] = useState();

    const dialCodeOptions = response?.map((country) => ({
        value: `${country.idd.root} ${country.idd.suffixes}`,
        label: `${ucwords(country.name.common)}`
    }));

    const fetchData = async () => {
        await fetch('https://restcountries.com/v3.1/all')
            .then((response) => response.json())
            .then((data) => {
                setResponse(data);
            });
    }

    useEffect(() => {
        fetchData();
    }, []);

    // Handle multi-select change and store selected values in an array
    const handleSelectChange = (selectedOptions) => {
        const selectedValues = multiple ? (selectedOptions ? selectedOptions.map(option => option.value) : []) : [];
        handleChange({ target: { name: id, value: (multiple ? selectedValues : selectedOptions.value) } });
    };

    // Map the selected dialCode values back to their label-value pair options
    const selectedValueOptions = dialCodeOptions?.filter(option => value?.includes(option?.value));

    return (
        <>
            {label ? (
                <label htmlFor={id} className={`form-label text-capitalize`}>{(label)} {required ? (<span className='text-danger'>*</span>) : ('')}</label>
            ) : ('')}
            <Select
                className={error ? `is-invalid` : ``}
                id={id}
                value={multiple ? selectedValueOptions : dialCodeOptions?.find(option => option.value === value)}
                options={dialCodeOptions}
                placeholder="Select Dial Code"
                onChange={handleSelectChange}
                styles={customStyles}
                isMulti={multiple}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectDialCode;
