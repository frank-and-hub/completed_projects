// planController.js
import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useLoading } from '../context/LoadingContext'
import { useFormValidation } from 'components/Admin/Form/FormValidation'
import { notifyError, notifyInfo, notifySuccess } from 'components/Admin/Comman/Notification/Notification'
import { initialState, submitPlan, validateForm } from '../models/planModel'
import SubmitButton from 'components/Admin/Form/SubmitButton'
import Textarea from 'components/Admin/Form/Textarea'
import SelectForm from 'components/Admin/Form/Select/SelectForm'
import Input from 'components/Admin/Form/Input'
import { OptionsPaymentMethod, OptionsPaymentType } from '../utils/selects'

export const CreatePlan = () => {
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    // Initial form state
    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, validateForm);

    const handleSubmit = async (e) => {
        e.preventDefault();
        notifyInfo(values);
        validateSubmit(e);

        if (errors && Object.keys(errors).length > 0) {
            // console.info(`Form validation failed :`);
            console.table(errors);
            return false;
        }

        // Submit the form data
        setLoading(true);
        try {
            const res = await submitPlan(values);
            if (res) {
                resetForm();
                notifySuccess(res.message);
            }
            navigate('/admin/plans', { replace: true });
        } catch (err) {
            notifyError(err.message);
        } finally {
            setLoading(false);
        }
    };

    // Reset the form after successful submission
    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    return (
        <div className={`card`}>
            <div className={`card-body`}>
                <form key={formKey} encType={`multipart/form-data`} className={`row mt-3 g-3 needs-validation`} onSubmit={handleSubmit} noValidate>
                    <Input name={`name`} label="Name" value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
                    <Input name={`price`} label="Price" value={values.price} onChange={handleChange} error={errors.price} required={true} inputType={true} />
                    <Input name={`currency`} label="Currency" value={values.currency} onChange={handleChange} error={errors.currency} required={true} inputType={true} />
                    <div className={`col-md-4`}>
                        <SelectForm id="payment_method" value={values.payment_method} handleChange={handleChange} error={errors.payment_method} required={true} label="Payment Method" Options={OptionsPaymentMethod} />
                    </div>
                    <div className={`col-md-4`}>
                        <SelectForm id="payment_type" value={values.payment_type} handleChange={handleChange} error={errors.payment_type} required={true} label="Payment Type" Options={OptionsPaymentType} />
                    </div>
                    <Textarea name={`description`} className={`w-100`} label="Description" value={values.description} onChange={handleChange} error={errors.description} required={true} />
                    <div className={`col-12`}>
                        <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
                    </div>
                </form>
            </div>
        </div>
    );
}

export const EditPlan = () => {

}

export const ViewPlan = () => {

}

