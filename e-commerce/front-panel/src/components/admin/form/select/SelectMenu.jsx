import React from 'react'
import Select from 'react-select'
import { useSelector } from 'react-redux'
import { customStyles, ucwords } from 'utils/helper'

function SelectMenu({ id, handleChange, value, error, required = false, disabled = false, label = null }) {

    const menuData = useSelector((state) => (state.menu.menuData));

    let menuOptions = menuData?.data?.map((val, index) => ({
        value: val?.id,
        label: (<><i className={val?.icon}></i> {ucwords(val?.name)}</>)
    }));

    menuOptions = [{
        value: '',
        label: `Select Menu`
    },
    ...menuOptions
    ]

    // const selectedMenuOptions = menuOptions?.filter(option => value?.includes(option?.value));
    const selectedMenuOptions = menuOptions?.find(option => option?.value === value);

    return (
        <>
            {label ? (
                <label htmlFor={id} className={`form-label text-capitalize`}>{(label)} {required ? (<span className='text-danger'>*</span>) : ('')}</label>
            ) : ('')}
            <Select
                className={``}
                id={id}
                options={menuOptions}
                value={selectedMenuOptions}
                placeholder="Select Menu"
                onChange={(selectedOption) => handleChange({ target: { name: id, value: selectedOption.value } })}
                styles={customStyles}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectMenu