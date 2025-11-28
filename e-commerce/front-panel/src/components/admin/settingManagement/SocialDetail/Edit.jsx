import React, { useEffect, useState } from 'react'
import { useDispatch } from 'react-redux'
import { useNavigate, useParams } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { notifyError, notifyInfo, notifySuccess } from 'components/admin/comman/notification/Notification'
import { formattedData } from 'utils/helper'
import { get, patch } from 'utils/AxiosUtils'
import { processNotifications } from 'utils/notificationUtils'
import Input from 'components/admin/form/Input'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import SubmitButton from 'components/admin/form/SubmitButton'
import CardForm from 'components/admin/card/CardForm'
import { socialDetailValidation, useFormValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        name: '',
        icon: '',
        url: ''
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
            const newValues = formattedData(values);
            const res = await patch(`/social-details/${id}`, newValues);
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

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [serviceData] = await Promise.all([
                    get(`/social-details/${id}/edit`),
                ]);
                setValues(serviceData?.data || {});
                processNotifications(200, serviceData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, setValues, id]);

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <Input name={`name`} label="Name" value={values?.name} required={true} error={errors.name} inputType={true} disabled={false} onChange={handleChange} />
            <div className={`col-md-4`}>
                <SelectIcon id="icon" value={values?.icon} handleChange={(e) => handleChange(e)} error={errors.icon} label='Icon' required={true} disabled={false} />
            </div>
            <Input name={`url`} text={`url`} label={`Url`} value={values?.url} required={true} error={errors.url} inputType={true} disabled={false} onChange={handleChange} />
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
