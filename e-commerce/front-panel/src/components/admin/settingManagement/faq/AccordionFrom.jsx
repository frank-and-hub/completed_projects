import React, { useState } from 'react';
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import Input from 'components/admin/form/Input'
import SubmitButton from 'components/admin/form/SubmitButton'
import { patch } from 'utils/AxiosUtils';
import { formattedData } from 'utils/helper'
import Textarea from 'components/admin/form/Textarea';
import { useLoading } from 'context/LoadingContext';
import { faqValidation, useFormValidation } from 'utils/FormValidation';
import { processNotifications } from 'utils/notificationUtils';
import { useDispatch } from 'react-redux';

function AccordionFrom({ value, onAction }) {

    const id = value?.id;
    const [formKey, setFormKey] = useState(0);
    const { loading, setLoading } = useLoading();
    const dispatch = useDispatch();

    const initialState = {
        question: value.question ?? '',
        answer: value.answer ?? ''
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, faqValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            const newValues = formattedData(values);
            const res = await patch(`/faq/${id}`, newValues);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
        } catch (err) {
            processNotifications(err.status || 500, err.message, dispatch);
        } finally {
            setLoading(false)
        }
    };

    const resetForm = () => {
        setValues(initialState);
        onAction(false);
        setFormKey((prevKey) => prevKey + 1);
    };

    return (
        <>
            <form key={formKey} encType={`multipart/form-data`} className={`row m-0 needs-validation`} onSubmit={handleSubmit} noValidate>
                <div className='d-none'>
                    <Input values={values?.question} name={`question`} onChange={handleChange} required={false} disabled={false} inputType={true} error={errors.question} />
                </div>
                <Textarea border={`0`} label={null} onChange={handleChange} className={`w-100`} name={`answer`} value={values?.answer} />
                <div className={`col-12 text-end`}>
                    <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
                </div>
            </form>
        </>
    );
}

export default AccordionFrom;