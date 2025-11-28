import React, { useEffect, useState } from 'react'
import SubmitButton from 'components/admin/form/SubmitButton'
import { get, post } from 'utils/AxiosUtils'
import { notifyError, notifySuccess } from 'components/admin/comman/notification/Notification'

import Textarea from 'components/admin/form/Textarea'
import { processNotifications } from 'utils/notificationUtils'
import { useDispatch } from 'react-redux'
import { useLoading } from 'context/LoadingContext'
import { pagesValidation, useFormValidation } from 'utils/FormValidation'
import Input from '../form/Input'

const ReturnPolicyPage = () => {
    const dispatch = useDispatch();
    const [inState, setInState] = useState(false);
    
    const [formKey, setFormKey] = useState(0);
    const [response, setResponse] = useState(null);
    const { loading, setLoading } = useLoading();
    const [error, setError] = useState(null);
    
    const type = 'return_policy';

    const initialState = {
        description: response?.description ?? '',
        type: response?.type ?? type
    };

    const {
        formData: values,
        errors,
        handleChange,
        handleSubmit: validateSubmit,
        setFormData: setValues
    } = useFormValidation(initialState, pagesValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();

        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            setError(errors);
            notifyError('Form validation failed', errors);
            return false;
        }
        setLoading(true)
        try {
            const res = await post('/pages', values);
            setResponse(res.data);
            setInState(false);
            notifySuccess(res.message)
        } catch (err) {
            notifyError(err.message)
            setError(err.message);
        } finally {
            setLoading(false)
        }
    };

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true)
            try {
                const reqData = await get(`/pages?type=${type}`);
                const fetchedData = reqData?.response?.data[0] || {};
                setResponse(fetchedData);
                setValues(fetchedData);
                processNotifications(200, reqData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
                setError(err.message || 'Failed to fetch data');
            } finally {
                setLoading(false)
                setInState(false);
            }
        };

        fetchData();
    }, [dispatch, setLoading, setValues, type]);

    useEffect(() => {
        setFormKey(response?.id);
    }, [response]);

    return (
        <>
            <section className={`section`}>
                <div className={`card-head`}>
                    <div className={`card-title`}>
                        <p className='py-0 m-0 btn' onClick={() => (setInState(!inState))} > Return And Refund Policy </p>
                    </div>
                </div>
                <div className={`card`}>
                    <div className={`card-body`}>
                        {!loading && (!inState
                            ? (<div className='py-3 px-2 text-horizontal' dangerouslySetInnerHTML={{ __html: values.description ?? response?.description }}></div>)
                            : (
                                <form key={formKey} encType={`multipart/form-data`} className={`row mt-1 g-4 needs-validation`} onSubmit={handleSubmit} noValidate>
                                    <Input name={`type`} type={`hidden`} value={values?.type} required={false} error={errors.type} inputType={false} disabled={false} onChange={handleChange} />
                                    <Textarea border={`0`} editor={true} name={`description`} className={`w-100`} onChange={handleChange} disabled={!inState} required={!inState} value={values?.description ?? response?.description} label={null} error={error?.description} />
                                    <div className={`col-12`}>
                                        {inState && (
                                            <SubmitButton className={`custom`} disable={!loading} name={loading ? 'Updating...' : 'Update Form'} />
                                        )}
                                    </div>
                                </form>
                            ))}
                    </div>
                </div>
            </section>
        </>
    )
}

export default ReturnPolicyPage