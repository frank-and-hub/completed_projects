import React, { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'

import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { checkFileValidation, formattedData } from 'utils/helper'
import { processNotifications } from 'utils/notificationUtils'
import { get, patch } from 'utils/AxiosUtils'
import { useLoading } from 'context/LoadingContext'
import { brandValidation, useFormValidation } from 'utils/FormValidation'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import Textarea from 'components/admin/form/Textarea'
import api from 'utils/api'
import config from '../../../config'
import CardForm from 'components/admin/card/CardForm'

function Edit() {
    const { id } = useParams();
    const dispatch = useDispatch();
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const [src, setSrc] = useState(``);
    const baseUrl = config.reactApiUrl;

    const initialState = {
        name: '',
        description: '',
        price: '',
        currency: '',
        payment_method: '',
        payment_type: '',
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, brandValidation);

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
            const res = await patch(`/brands/${id}`, newValues);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/brands', { replace: true })
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
                    get(`/brands/${id}/edit`),
                ]);

                setValues(serviceData?.data || {});
                setSrc(serviceData?.data?.image?.path);
                processNotifications(200, serviceData?.message, dispatch);
            } catch (err) {
                processNotifications(err.status || 500, err.message, dispatch);
            }
        };
        if (id) {
            fetchData();
        }
    }, [dispatch, setValues, id]);

    const handleFileUpload = async (e) => {
        const formData = checkFileValidation(e) ? e.target.files[0] : null;
        const imageUrl = URL.createObjectURL(formData);
        setSrc(imageUrl);
        values.image = formData;
        await api({
            method: 'post',
            url: `${baseUrl}/brands/${id}/image`,
            headers: {
                'Content-Type': 'multipart/form-data',
            }, data: values
        });
    };

    const handleClick = (e) => {
        document.getElementById('imageInput').click();
    };

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Brand`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={handleClick} loading={`lazy`}/>
                </div>
            </div>
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={handleFileUpload} />
            <Input name={`name`} label="Name" value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} error={errors.description} required={true} inputType={true} ></Textarea>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
            </div>
        </CardForm>
    );
}

export default Edit;
