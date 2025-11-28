import React, { useEffect, useState } from 'react'
import Select from 'react-select'
import { customStyles, ucwords } from 'utils/helper'
import { useDispatch, useSelector } from 'react-redux';
import { icons } from '../../comman/Icons';
import { setIcon } from 'store/iconSlice';

function SelectIcon({ id, handleChange, value, error, required = false, disabled = false, label = null }) {
    const dispatch = useDispatch();
    const iconDataOptions = useSelector((state) => (state?.icon?.iconData));
    const [iconOptions, setIconOptions] = useState([]);
    const [currentPage, setCurrentPage] = useState(1); const [hasMore, setHasMore] = useState(true);
    const ITEMS_PER_PAGE = 100;

    useEffect(() => {
        const fetchIcons = async () => {
            if (!iconDataOptions || iconDataOptions.length === 0) {
                if (!hasMore) return;
                const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
                const endIndex = startIndex + ITEMS_PER_PAGE;

                const fetchedIconOptions = icons.slice(startIndex, endIndex).map((icon) => ({
                    value: icon,
                    label: (<><i className={icon}></i> {ucwords(icon)}</>),
                }));
                if (endIndex >= icons.length) {
                    setHasMore(false);
                } else {
                    setCurrentPage(currentPage + 1);
                }
                setIconOptions((prevOptions) => [...prevOptions, ...fetchedIconOptions]);
                dispatch(setIcon([...iconOptions, ...fetchedIconOptions]));
            } else {
                setIconOptions(iconDataOptions);
            }
        };

        fetchIcons();
    }, [iconDataOptions, currentPage, hasMore, iconOptions, dispatch]);

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
                options={iconOptions}
                value={iconOptions?.find(option => option.value === value)}
                placeholder="Select an icon"
                onChange={handleSelectChange}
                styles={customStyles}
                isDisabled={disabled}
            />
            {error && <div className={`invalid-feedback`}>{error}</div>}
        </>
    )
}

export default SelectIcon