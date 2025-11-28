import React, { useEffect, useMemo, useState, useTransition } from 'react'
import api from 'utils/api'
import config from '../../../config'
import Input from 'components/admin/form/Input'
import SubmitButton from 'components/admin/form/SubmitButton'
import Textarea from 'components/admin/form/RoundedTextarea'
import { useAuth } from 'utils/AuthContext'
import { checkFileValidation } from 'utils/helper'
import { notifyError, notifySuccess } from 'components/admin/comman/notification/Notification'
import { get } from 'utils/AxiosUtils'
import { aboutProfileValidation, useFormValidation } from 'utils/FormValidation'

function AboutProfile() {

    const baseUrl = config.reactApiUrl;

    const { loadUser } = useAuth();
    const [isPending, startTransition] = useTransition();
    const [formKey, setFormKey] = useState(0);
    const [about, setAbout] = useState({});
    const [resumeFile, setResumeFile] = useState(0);

    const [src, setSrc] = useState('/img/file-earmark-arrow-up.svg');

    const initialState = useMemo(() => ({
        title: about?.title ?? '',
        bio: about?.bio ?? '',
        experience: about?.experience ?? '',
    }), [about]);

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, aboutProfileValidation);

    const handleSubmit = async (e) => {
        e.preventDefault();
        validateSubmit(e);
        
        if (errors && Object.keys(errors).length !== 0) {
            console.table(errors);
            return false;
        }

        startTransition(async () => {
            try {
                const res = await api({
                    method: 'post',
                    url: `${baseUrl}/about-details`,
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    }, data: { ...values, resume: resumeFile }
                });
                if (!res) throw new Error("Failed to submit form");
                setSrc(res.data.data.resume);
                loadUser();
                resetForm()
                notifySuccess(res.data.message);
            } catch (error) {
                notifyError(`Error during updating user profile data: ${error}`);
            }
        });
    }

    const handleClick = (e) => {
        document.getElementById('resumeFile').click();
    };

    const resetForm = () => {
        setValues(initialState);
        setFormKey((prevKey) => prevKey + 1);
        document.getElementsByTagName('form')[0].reset();
    };

    const handleFileUpload = async (e) => {
        const formData = checkFileValidation(e) ? e.target.files[0] : null;
        setResumeFile(formData);
    };

    useEffect(() => {
        const fetchData = async () => {
            try {
                const abountRes = await get(`/about-details`);
                setAbout(abountRes?.response?.data);
                setValues(initialState);
                setSrc(abountRes?.response?.data?.resume?.path);
            } catch (error) {
                notifyError(`Error during getting data: ${error}`);
            }
        }
        if (!about) fetchData();
    }, [about, setValues, initialState]);

    return (
        <>
            <form key={formKey} encType={`multipart/form-data`} onSubmit={handleSubmit}>
                <div className={`row mb-3`}></div>
                <Input name={`title`} label="title" value={values?.title} onChange={handleChange} error={errors.title} required={true} />
                <Textarea name={`bio`} label="bio" value={values?.bio} onChange={handleChange} error={errors.bio} required={true} />
                <Textarea name={`experience`} label="experienc" value={values?.experience} onChange={handleChange} error={errors.experience} required={true} />

                <div className={`row mb-3`}>
                    <label htmlFor={`fileResume`} className={`col-md-4 col-lg-3 col-form-label text-start text-capitalize`}>Resume <span className={`text-dange`}>*</span></label>
                    <div className={`col-md-8 col-lg-9`}>
                        <img src={src} alt={`Resume`} id={`fileResume`} className={`w-25`} style={{ cursor: 'pointer'}} onClick={handleClick} loading={`lazy`} />
                        <input type={`file`} id={`resumeFile`} className={`d-none`} name={`image`} onChange={handleFileUpload} />
                    </div>
                </div>

                <div className={`text-center`}>
                    <SubmitButton className={`custom`} disable={isPending} name={isPending ? 'Saving...' : 'Save Changes'} />
                </div>
            </form>
        </>
    )
}

export default AboutProfile