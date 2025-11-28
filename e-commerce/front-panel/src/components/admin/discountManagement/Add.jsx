import React, { useState } from 'react'

import { post } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'
import { useFormValidation, discountValidation } from 'utils/FormValidation'

function Add() {

    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const navigate = useNavigate();
    const initialState = {
        name: '',
        percentage: '',
        description: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, discountValidation);

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
            const res = await post('/discounts', values);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/products/discounts', { replace: true })
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
            <Input name={`percentage`} label="percentage" value={values.percentage} onChange={handleChange} error={errors.percentage} required={true} inputType={true} />
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} error={errors.description} required={true} inputType={true} ></Textarea>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
            </div>
        </CardForm>
    );
}

export default Add;
