import React, { useEffect, useState, useTransition } from 'react'

import { patch } from 'utils/AxiosUtils'
import validate from './validate'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/RoundedTextarea'
import { notifyError, notifySuccess } from 'components/admin/comman/notification/Notification'
import { useAuth } from 'utils/AuthContext'
import { useFormValidation } from 'utils/FormValidation'
// import SelectForm from 'components/admin/form/select/SelectForm'
// import { GenderOptions } from 'utils/selects'

function ProfileForm({ user }) {
    const { loadUser } = useAuth();
    const [isPending, startTransition] = useTransition();
    const [formKey, setFormKey] = useState(0);
    const userId = user?._id;

    const initialState = {
        first_name: user?.name?.first_name ?? '',
        middle_name: user?.name?.middle_name ?? '',
        last_name: user?.name?.last_name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        gender: user?.gender ?? '',
        about: user?.about ?? '',
        zipcode: user?.zipcode ?? '',
        address: user?.address ?? '',
        city: user?.city ?? '',
        state: user?.state ?? '',
        timezone: user?.timezone ?? '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, validate);

    const handleSubmit = async (e) => {
        e.preventDefault();

        validateSubmit(e);

        if (errors && Object.keys(errors).length !== 0) {
            console.table(errors);
            return false;
        }

        const convertedData = Object.entries(values).map(([key, value]) => ({
            propName: key,
            value: value
        }));

        startTransition(async () => {
            try {
                const response = await patch(`/users/${userId}`, convertedData);
                if (!response) throw new Error("Failed to submit form");
                loadUser();
                resetForm()
                notifySuccess(response.message);
            } catch (error) {
                notifyError(`Error during updating user profile data: ${error}`);
            }
        });
    }

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    useEffect(() => {
        if (!user) loadUser();
        // } else {
        //   setValues(initialState);
        // }
    }, [user, loadUser]);


    return (
        <>
            <form key={formKey} encType={`multipart/form-data`} onSubmit={handleSubmit}>
                <div className={`mb-3 col-12 row w-100`}></div>
                <Input name={`first_name`} label={`First Name`} value={values?.first_name} onChange={handleChange} error={errors?.first_name} required={true} />
                <Input name={`middle_name`} label={`Middle Name`} value={values?.middle_name} onChange={handleChange} error={errors?.middle_name} required={true} />
                <Input name={`last_name`} label={`Last Name`} value={values?.last_name} onChange={handleChange} error={errors?.last_name} required={true} />
                <Input name={`phone`} label={`Phone`} value={values?.phone} onChange={handleChange} error={errors?.phone} required={true} />
                <Input name={`email`} label={`Email`} type={`email`} value={values?.email} onChange={handleChange} error={errors?.email} required={true} />

                <Input name={`gender`} label={`Gender`} value={values?.gender} onChange={handleChange} error={errors?.gender} required={true} />
                
                <Textarea name={`about`} label={`About`} value={values?.about} onChange={handleChange} error={errors?.about} required={true} />
                <Input name={`address`} label={`Address`} value={values?.address} onChange={handleChange} error={errors?.address} required={true} />
                <Input name={`city`} label={`City`} value={values?.city} onChange={handleChange} error={errors?.city} required={true} />
                <Input name={`state`} label={`State`} value={values?.state} onChange={handleChange} error={errors?.state} required={true} />
                <Input name={`zipcode`} label={`Zip Code`} value={values?.zipcode} onChange={handleChange} error={errors?.zipcode} required={true} />
                
                <Input name={`tmezone`} label={`Tme Zone`} value={values?.tmezone} onChange={handleChange} error={errors?.tmezone} required={true} />
                
                <div className={`text-center`}>
                    <SubmitButton className={`custom`} disable={isPending} name={isPending ? 'Saving...' : 'Save Changes'} />
                </div>
            </form>
        </>
    )
}

export default ProfileForm