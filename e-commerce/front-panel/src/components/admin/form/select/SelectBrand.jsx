import React, { useState, useEffect } from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'
import { get } from 'utils/AxiosUtils';

function SelectBrand({ id, handleChange, value, error, label = null, required = false, disabled = false, }) {
    const [response, setResponse] = useState();

    let brandOptions = response?.data?.map((item) => ({
        value: item?.id,
        label: `${ucwords(item?.name)}`
    }));

    const fetchData = async () => {
        const res = await get('/brands?page=0');
        setResponse(res?.response);
    }

    useEffect(() => {
        fetchData();
    }, []);

    const handleSelectChange = (selectedOption) => {
        handleChange({ target: { name: id, value: selectedOption.value } });
    };

    return (
        <>
            {label ? (
                <label htmlFor={id} className={`form-label text-capitalize`}>{(label)} {required ? (<span className='text-danger'>*</span>) : ('')}</label>
            ) : ('')}
            <Select
                className={error ? `is-invalid` : ``}
                id={id}
                options={brandOptions}
                value={brandOptions?.find(option => option.value === value)}
                placeholder={`Select Brand`}
                onChange={handleSelectChange}
                styles={customStyles}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectBrand;
