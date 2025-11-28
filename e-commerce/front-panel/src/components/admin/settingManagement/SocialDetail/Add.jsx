import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { post } from 'utils/AxiosUtils'
import { useLoading } from 'context/LoadingContext'
import Input from 'components/admin/form/Input'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import SubmitButton from 'components/admin/form/SubmitButton'
import { notifyError, notifyInfo, notifySuccess } from 'components/admin/comman/notification/Notification'
import CardForm from 'components/admin/card/CardForm'
import { socialDetailValidation, useFormValidation } from 'utils/FormValidation'

function Add() {
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        name: '',
        icon: '',
        url: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, socialDetailValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        notifyInfo(values);
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            const res = await post('/social-details', values);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/settings/social-details', { replace: true })
        } catch (err) {
            notifyError(err.message)
        } finally {
            setLoading(false)
        }
    };

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <Input name={`name`} label="Name" value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectIcon id="icon" value={values.icon} handleChange={handleChange} error={errors.icon} required={true} disabled={false} label='Icon' />
            </div>
            <Input name={`url`} text={`url`} label="Url" value={values.url} onChange={handleChange} error={errors.url} required={true} inputType={true} />
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
            </div>
        </CardForm>
    );
}

export default Add;
