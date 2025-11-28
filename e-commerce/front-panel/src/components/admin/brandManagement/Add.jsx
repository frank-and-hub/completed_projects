import React, { useEffect, useState } from 'react'

import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import Textarea from 'components/admin/form/Textarea'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { checkFileValidation } from 'utils/helper'
import config from '../../../config'
import api from 'utils/api'
import CardForm from 'components/admin/card/CardForm'
import { brandValidation, useFormValidation } from 'utils/FormValidation'

function Add() {
    const navigate = useNavigate();
    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const [src, setSrc] = useState(``);
    const baseUrl = config.reactApiUrl;

    const initialState = {
        name: '',
        description: '',
        image: '',
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
            const res = await api({
                method: 'post',
                url: `${baseUrl}/brands`,
                headers: {
                    'Content-Type': 'multipart/form-data',
                }, data: values
            });
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

    const handleFileUpload = async (e) => {
        const formData = checkFileValidation(e) ? e.target.files[0] : null;
        const imageUrl = URL.createObjectURL(formData);
        setSrc(imageUrl);
        values.image = formData;
    };

    const handleClick = (e) => {
        document.getElementById('imageInput').click();
    };

    useEffect(() => {
        setSrc(src !== '' ? src : `/admin/img/profile-img.jpg`);
    }, [src]);

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <div className={`col-md-4`}>
                <div className='cursor-none'>
                    <img src={src} alt={`Brand`} className={`rounded-50 col-md-6 image-shell w-50`} onClick={handleClick} loading={`lazy`}/>
                </div>
            </div>
            <Input name={`name`} label="Name" value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <input type={`file`} id={`imageInput`} className={`d-none`} name={`image`} onChange={handleFileUpload} />
            <Textarea name={`description`} className={`w-100`} label="Description" value={values?.description} onChange={handleChange} error={errors.description} required={true} inputType={true} ></Textarea>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
            </div>
        </CardForm>
    );
}

export default Add;
