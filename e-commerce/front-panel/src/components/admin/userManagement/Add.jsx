import React, { useState } from 'react'
import Input from 'components/admin/form/Input'
import SubmitButton from 'components/admin/form/SubmitButton'
import SelectRole from 'components/admin/form/select/SelectRole'
import { post } from 'utils/AxiosUtils'
import { notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { useFormValidation, userValidation } from 'utils/FormValidation'
import SelectDialCode from 'components/admin/form/select/SelectDialCode'

function Add() {
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [response, setResponse] = useState(null);
    const [error, setError] = useState(null);
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        first_name: '',
        middle_name: '',
        last_name: '',
        email: '',
        password: '',
        dial_code:``,
        phone: '',
        role_id: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, userValidation);

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
            const res = await post('/users', values);
            setResponse(res);
            resetForm()
            notifySuccess(res.message)
            navigate('/admin/users', { replace: true })
        } catch (err) {
            console.error(err.message);
            setError(err.message);
        } finally {
            setLoading(false)
        }
    };

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    if (response && error) console.info(response && error);

    return (
        <><div key={formKey} className={`card`}>
            <div className={`card-body`}>
                <form encType={`multipart/form-data`} className={`row mt-3 g-4 needs-validation`} onSubmit={handleSubmit} noValidate>
                    <Input name={`first_name`} label="User First Name" value={values.first_name} onChange={handleChange} error={errors.first_name} required={true} inputType={true} />
                    <Input name={`middle_name`} label="Middle Name" value={values.middle_name} onChange={handleChange} error={errors.middle_name} required={false} inputType={true} />
                    <Input name={`last_name`} label="Last Name" value={values.last_name} onChange={handleChange} error={errors.last_name} required={true} inputType={true} />
                    <Input name={`email`} label="Email" type={`email`} value={values.email} onChange={handleChange} error={errors.email} required={true} inputType={true} />
                    <div className={`col-md-4`}>
                        <SelectDialCode id="dial_code" label={`dial_code`} value={values.dial_code} handleChange={handleChange} error={errors.dial_code} required={false} />
                    </div>
                    <Input name={`password`} label="Password" value={values.password} onChange={handleChange} error={errors.password} required={true} inputType={true} />
                    <Input name={`phone`} label="Phone" value={values.phone} onChange={handleChange} error={errors.phone} required={true} inputType={true} />
                    <div className={`col-md-4`}>
                        <SelectRole id={`role_id`} label={`Role`} value={values.role_id} handleChange={handleChange} error={errors.role_id} required={true} />
                    </div>
                    <div className={`col-12`}>
                        <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
                    </div>
                </form>
            </div>
        </div>
        </>
    );
}

export default Add;
