import React, { useState, useEffect } from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'
import { get } from 'utils/AxiosUtils';

function SelectTag({ id, handleChange, value, error, label = null, required = false, disabled = false, multiple = false }) {
    const [response, setResponse] = useState();

    const tagOptions = response?.data?.map((item) => ({
        value: item?.id,
        label: `${ucwords(item?.name)}`
    }));

    const fetchData = async () => {
        const res = await get('/tags?page=0');
        setResponse(res?.response);
    }

    useEffect(() => {
        fetchData();
    }, []);

    // Handle multi-select change and store selected values in an array
    const handleSelectChange = (selectedOptions) => {
        const selectedValues = multiple ? (selectedOptions ? selectedOptions.map(option => option.value) : []) : [];
        handleChange({ target: { name: id, value: (multiple ? selectedValues : selectedOptions.value) } });
    };

    // Map the selected tag values back to their label-value pair options
    const selectedValueOptions = tagOptions?.filter(option => value?.includes(option?.value));

    return (
        <>
            {label ? (
                <label htmlFor={id} className={`form-label text-capitalize`}>{(label)} {required ? (<span className='text-danger'>*</span>) : ('')}</label>
            ) : ('')}
            <Select
                className={error ? `is-invalid` : ``}
                id={id}
                value={multiple ? selectedValueOptions : tagOptions?.find(option => option.value === value)}
                options={tagOptions}
                placeholder="Select Tags"
                onChange={handleSelectChange}
                styles={customStyles}
                isMulti={multiple}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectTag;
