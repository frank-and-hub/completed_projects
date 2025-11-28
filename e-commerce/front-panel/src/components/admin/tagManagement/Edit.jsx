import React, { useEffect, useState } from 'react'
import { get, patch } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { processNotifications } from 'utils/notificationUtils'
import { formattedData } from 'utils/helper'
import { useLoading } from 'context/LoadingContext'
import CardForm from 'components/admin/card/CardForm'
import { tagValidation, useFormValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        name: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, tagValidation);

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
            const res = await patch(`/tags/${id}`, newValues);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/products/tags', { replace: true })
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
                const [tagData] = await Promise.all([
                    get(`/tags/${id}/edit`),
                ]);
                setValues(tagData?.data || {});
                processNotifications(200, tagData?.message, dispatch);
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
            <Input name={`name`} label="Name" value={values?.name} onChange={handleChange} required={true} error={errors.name} inputType={true} disabled={false} />

            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
