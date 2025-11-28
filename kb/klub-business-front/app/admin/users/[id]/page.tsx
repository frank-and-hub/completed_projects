"use client";

import { getUserDetails } from '@/api/users';
import InputText from '@/components/inputs/InputText';
import CommonSelect from '@/components/selects/CommonSelect';
import StaticSelect from '@/components/selects/StaticSelect';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { formStyle } from '@/utils/style';
import { use, useEffect } from 'react';

export default function ViewUsers({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { setFormData } = useFormContext();
    const { setLoading, setPageTitle } = useAuth();
    const { id } =  use(params);

    useEffect(() => {
        const fetchUser = async () => {
            setLoading(true);
            try {
                const user = await getUserDetails(id);
                setPageTitle(user.firstName);
                setFormData(user);
            } catch (err) {
                console.error('Failed to fetch user details:', err);
            } finally {
                setLoading(false);
            }
        };
        fetchUser();
    }, [id]);

    return (
        <form className={formStyle}>
            <InputText name={`firstName`} label={`first name`} type={`text`} readOnly />
            <InputText name={`middleName`} label={`middle name`} type={`text`} readOnly />
            <InputText name={`lastName`} label={`last name`} type={`text`} readOnly />
            <InputText name={`email`} label={`email`} type={`email`} readOnly />
            <CommonSelect id={`roleId`} label={`role`} apiUrl={`/v1/common-data?type=role`} readOnly />
            <InputText name={`phone`} label={`phone`} type={`tel`} maxLength={12} minLength={9} readOnly />
            <InputText name={`dateOfBirth`} label={`date of birth`} type={`date`} readOnly />
            {/* <CommonSelect id={`countryId`} label={`country`} apiUrl={`/v1/common-data?type=country`} readOnly /> */}
            <StaticSelect name={`gender`} label={`gender`} readOnly />
            <StaticSelect name={`deviceType`} label={`device type`} readOnly />
            <StaticSelect name={`relationshipStatus`} label={`relationship status`} readOnly />
        </form>
    );
}
