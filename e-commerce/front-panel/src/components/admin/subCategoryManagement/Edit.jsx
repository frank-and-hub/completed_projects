import React, { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { processNotifications } from 'utils/notificationUtils'
import { useLoading } from 'context/LoadingContext'
import { get, patch } from 'utils/AxiosUtils'
import { formattedData } from 'utils/helper'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import SubmitButton from 'components/admin/form/SubmitButton'
import Textarea from 'components/admin/form/Textarea'
import Input from 'components/admin/form/Input'
import SelectCategory from 'components/admin/form/select/SelectCategory'
import CardForm from 'components/admin/card/CardForm'
import { subCategoryValidation, useFormValidation } from 'utils/FormValidation'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);

    const initialState = {
        name: '',
        icon: '',
        category: '',
        description: ''
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, subCategoryValidation);

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
            const res = await patch(`/sub-categories/${id}`, newValues);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/products/sub-categories', { replace: true })
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
                const [getData] = await Promise.all([
                    get(`/sub-categories/${id}/edit`),
                ]);
                setValues(getData?.data || {});
                processNotifications(200, getData?.message, dispatch);
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
            <Input name={`name`} label={`Name`} value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectIcon id={`icon`} label={`icon`} value={values.icon} handleChange={handleChange} error={errors.icon} required={true} />
            </div>
            <div className={`col-md-4`}>
                <SelectCategory id={`category`} label={`category`} value={values.category} handleChange={handleChange} error={errors.category} required={true} multiple={false} />
            </div>
            <Textarea name={`description`} className={`w-100`} label={`Description`} value={values?.description} onChange={handleChange} error={errors.description} required={true} inputType={true} ></Textarea>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
