'use client';

import { createNewUser } from '@/api/users';
import InputText from '@/components/inputs/InputText';
import CommonSelect from '@/components/selects/CommonSelect';
import StaticSelect from '@/components/selects/StaticSelect';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { cl } from '@/utils/console';
import { get18YearsAgoDate } from '@/utils/helpers';
import { formStyle } from '@/utils/style';
import { useEffect } from 'react';

export default function AddUser() {
    const { loading, setLoading, setPageTitle } = useAuth();
    const { handleSubmit, SubmitBtn } = useFormContext();
    
    useEffect(() => {
        setPageTitle(`Add New User`);
    }, []);

    return (
        <form onSubmit={(e) => {
            e.preventDefault();
            handleSubmit({
                api: createNewUser,
                setLoading,
                onSuccess: () => cl(`Successfully created new user`)
            });
        }} className={formStyle}>
            <InputText name={`firstName`} label={`first name`} type={`text`} required />
            <InputText name={`middleName`} label={`middle name`} type={`text`} />
            <InputText name={`lastName`} label={`last name`} type={`text`} />
            <InputText name={`email`} label={`email`} type={`email`} required />
            <CommonSelect id={`roleId`} label={`role`} apiUrl={`/v1/common-data?type=role`} />
            <InputText name={`phone`} label={`phone`} type={`tel`} required maxLength={12} minLength={9} />
            <InputText name={`dateOfBirth`} label={`date of birth`} type={`date`} max={get18YearsAgoDate()} />
            <CommonSelect id={`countryId`} label={`country`} apiUrl={`/v1/common-data?type=country`} />
            <StaticSelect name={`gender`} label={`gender`} required />
            <StaticSelect name={`deviceType`} label={`device type`} />
            <StaticSelect name={`relationshipStatus`} label={`relationship status`} />
            <SubmitBtn name={loading ? `Submiting...` : `Submit`} label={`Save`} />
        </form>
    );
}
