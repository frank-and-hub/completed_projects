import InputText from '@/components/inputs/InputText';
import CommonSelect from '@/components/selects/CommonSelect'
import StaticSelect from '@/components/selects/StaticSelect'
import { useFormContext } from '@/context/FormContext';
import { formClass } from '@/utils/style';
import React, { useEffect } from 'react'

interface UseFilterProps {
    setFilters: React.Dispatch<React.SetStateAction<Record<string, any>>>;
    visible: boolean;
}

export default function UseFilter({ setFilters, visible }: UseFilterProps) {
    const { formData } = useFormContext();

    useEffect(() => {
        setFilters(formData);
    }, [formData, setFilters]);

    if (!visible) return null;

    return (
        <form className={`${formClass} ${visible ? 'scale-y-100 opacity-100' : 'scale-y-0 opacity-0'}`}>
            <StaticSelect name={`status`} label={`status`} placeholder={`select status`} />
            <StaticSelect name={`gender`} label={`gender`} placeholder={`select gender`} />
            <CommonSelect id={`role`} label={`role`} apiUrl={`/v1/common-data?type=role`} placeholder={`select role`} />
            <StaticSelect name={`deviceType`} label={`device type`} placeholder={`select device`} />
            <StaticSelect name={`relationshipStatus`} label={`relationship status`} placeholder={`select relationship`} />
        </form>
    )
};