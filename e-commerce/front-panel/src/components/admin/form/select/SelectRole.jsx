import React, { useEffect, useState } from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'
import { get } from 'utils/AxiosUtils';

function SelectRole({ id, handleChange, value, error, required = false, disabled = false, label = null }) {

    const [response, setResponse] = useState();

    let roleOptions = response?.data?.map((item) => ({
        value: item?.id,
        label: `${ucwords(item?.name)}`
    }));

    const fetchData = async () => {
        const res = await get('/roles?page=0');
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
                options={roleOptions}
                value={roleOptions?.find(option => option.value === value)}
                placeholder={`Select role`}
                onChange={handleSelectChange}
                styles={customStyles}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectRole;