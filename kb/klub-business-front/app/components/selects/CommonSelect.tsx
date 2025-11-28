'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { Select } from '@mantine/core';
import { useFormContext } from '@/context/FormContext';
import { ArrowPathIcon } from '@heroicons/react/24/outline';
import { cap, debounce, sleep } from '@/utils/helpers';
import { CommonSelectProps } from '@/types/CommonQueryParams';

// Redux
import { useSelector, useDispatch } from 'react-redux';
import { AppDispatch, RootState } from '@/store';
import { fetchSelectOptions } from '@/store/slices/selectOptionsSlice';
import { inputLabel, radius, selectStyle } from '@/utils/style';

export default function CommonSelect({
  id,
  label,
  placeholder,
  required = false,
  apiUrl,
  labelKey = 'name',
  valueKey = 'id',
  searchable = false,
  ...rest
}: CommonSelectProps) {
  const dispatch = useDispatch<AppDispatch>();

  const { formData, handleChange, errors } = useFormContext();

  const url = new URL(apiUrl, 'http://localhost:3000');
  const key = url.searchParams.get('type') || 'default';

  const options = useSelector((state: RootState) => state.select.options[key] || []);
  const loading = useSelector((state: RootState) => state.select.loading[key] || false);
  const hasMore = useSelector((state: RootState) => state.select.hasMore[key] ?? true);
  const page = useSelector((state: RootState) => state.select.page[key] || 1);

  const [localSearch, setLocalSearch] = useState('');
  const searchKey = localSearch.trim();

  const dispatchFetch = useCallback(() => {
    if (!loading && (hasMore || options.length === 0)) {
      sleep(20000);
      dispatch(fetchSelectOptions({ key, url: apiUrl, labelKey, valueKey, page, search: searchKey }));
    }
  }, [key, apiUrl, labelKey, valueKey, page, hasMore, loading, searchKey]);

  useEffect(() => {
    const debounced = debounce(() => {
      dispatchFetch();
    }, 500);
    debounced();
  }, [dispatchFetch]);

  const loaderIcon = <ArrowPathIcon className={`w-4 h-4 animate-spin`} />;

  return (
    <div className={`mb-4`}>
      <Select
        id={id}
        label={
          label && required ? (
            <>
              {cap(label)} <span className={`text-red-500`}>*</span>
            </>
          ) : (
            label ? cap(label) : ''
          )
        }
        placeholder={placeholder ? cap(placeholder) : `Select ${label || id}`}
        data={[{ value: '', label: 'Select option' }, ...options]}
        value={formData[id]?.toString() || formData[label]?.id.toString() || ''}
        onChange={(value) => handleChange({ target: { name: id, value } })}
        error={errors?.[id]}
        size={`sm`}
        radius={radius}
        allowDeselect
        name={id}
        searchable={searchable}
        checkIconPosition={`right`}
        rightSection={loading ? loaderIcon : undefined}
        onSearchChange={setLocalSearch}
        nothingFoundMessage={
          loading ? 'Loading more options...' : 'No options found'
        }
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