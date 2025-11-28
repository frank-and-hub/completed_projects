import React, { useEffect, useState } from 'react'
import Input from 'components/admin/form/Input'
import SubmitButton from 'components/admin/form/SubmitButton'
import SelectRole from 'components/admin/form/select/SelectRole'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { notifyInfo, notifySuccess } from 'components/admin/comman/notification/Notification'
import { get, patch } from 'utils/AxiosUtils'
import { processNotifications } from 'utils/notificationUtils'
import { formattedData } from 'utils/helper'
import { useLoading } from 'context/LoadingContext'
import CardForm from 'components/admin/card/CardForm'
import { useFormValidation, userValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
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
            const newValues = formattedData(values);
            const res = await patch(`/users/${id}`, newValues);
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

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [userData] = await Promise.all([
                    get(`/users/${id}/edit`),
                ]);
                setValues(userData?.data || {});
                processNotifications(200, userData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, id, setValues]);

    if (response && error) console.info(response && error);

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <Input name={`first_name`} label="User First Name" value={values?.name?.first_name} onChange={handleChange} error={errors.first_name} required={true} inputType={true} />
            <Input name={`middle_name`} label="Middle Name" value={values?.name?.middle_name} onChange={handleChange} error={errors.middle_name} required={true} inputType={true} />
            <Input name={`last_name`} label="Last Name" value={values?.name?.last_name} onChange={handleChange} error={errors.last_name} required={true} inputType={true} />
            <Input name={`email`} label="Email" type={`email`} value={values?.email} onChange={handleChange} error={errors.email} required={true} inputType={true} />
            <Input name={`password`} label="Password" value={values?.password} onChange={handleChange} error={errors.password} required={true} inputType={true} />
            <Input name={`phone`} label="Phone" value={values?.phone} onChange={handleChange} error={errors.phone} required={true} inputType={true} />

            <div className={`col-md-4`}>
                <SelectRole id={`role_id`} value={values?.role_id} required={true} disabled={false} label='role' handleChange={handleChange} />
            </div>

            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit