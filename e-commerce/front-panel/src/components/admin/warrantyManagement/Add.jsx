import React, { useState } from 'react'
import { post } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import SelectForm from 'components/admin/form/select/SelectForm'
import { PeriodOptions } from 'utils/selects'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'
import { useFormValidation, warrantyValidation } from 'utils/FormValidation'

function Add() {

    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const navigate = useNavigate();
    const initialState = {
        name: '',
        duration: '',
        period: '',
        description: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, warrantyValidation);

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
            const res = await post('/warranties', values);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/warranties', { replace: true })
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
            <Input name={`duration`} label="duration" value={values.duration} onChange={handleChange} error={errors.duration} required={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectForm id="period" label={`Period`} value={values.period} handleChange={handleChange} error={errors.period} required={true} Options={PeriodOptions} />
            </div>
            <Textarea onChange={handleChange} className={`w-100`} name={`description`} value={values.description} error={errors.description} label={`Description`} required={true} disabled={false} editor={true} />
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
            </div>
        </CardForm>
    );
}

export default Add;
