import React, { useEffect, useState } from 'react'
import { useFormValidation } from 'components/admin/form/FormValidation'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import SelectIcon from 'components/admin/form/Select/SelectIcon'
import { formattedData } from 'utils/helper'
import { processNotifications } from 'utils/notificationUtils'
import { get, patch } from 'utils/AxiosUtils'
import Textarea from 'components/admin/form/Textarea'
import { useLoading } from 'context/LoadingContext'
import CardForm from 'components/admin/card/CardForm'
import { serviceValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        name: '',
        icon: '',
        description: ''
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, serviceValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        notifyInfo(values);
        validateSubmit(e);
        if (errors && Object.keys(errors).length > 0) {
            console.info(`Form validation failed : `);
            console.table(errors);
            return false;
        }
        setLoading(true)
        try {
            const newValues = formattedData(values);
            const res = await patch(`/services/${id}`, newValues);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/services', { replace: true })
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
                    get(`/services/${id}`),
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
            <Textarea onChange={handleChange} name={`description`} error={errors.description} value={values?.description} label={`Description`} required={true} disabled={false} />

            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
