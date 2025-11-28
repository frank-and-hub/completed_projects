import React, { useState } from 'react'
import { post } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifyError, notifySuccess, notifyInfo } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import Textarea from 'components/admin/form/Textarea'
import CardForm from 'components/admin/card/CardForm'
import { storeValidation, useFormValidation } from 'utils/FormValidation'
import SelectSupplier from 'components/admin/form/select/SelectSupplier'

function Add() {

    const { loading, setLoading } = useLoading();
    const [formKey, setFormKey] = useState(0);
    const navigate = useNavigate();
    const initialState = {
        name: ``,
        owner_name:``,
        phone: ``,
        email: ``,
        address: ``,
        city: ``,
        state: ``,
        zipcode: ``,
        country: ``,
        supplier_id: ``
    };

    const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, storeValidation);

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
            const res = await post('/warehouses', values);
            if (res) {
                resetForm()
                notifySuccess(res.message)
            }
            navigate('/admin/storage/warehouses', { replace: true })
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

    return (
        <CardForm handleSubmit={handleSubmit} key={formKey}>
            <Input name={`name`} label="name" value={values.name} onChange={handleChange} error={errors.name} required={true} inputType={true} />
            <Input name={`owner_name`} label="owner_name" value={values.owner_name} onChange={handleChange} error={errors.owner_name} required={true} inputType={true} />
            <Input name={`phone`} label="phone" value={values.phone} onChange={handleChange} error={errors.phone} required={true} inputType={true} />
            <Input name={`email`} label="email" value={values.email} onChange={handleChange} error={errors.email} required={true} inputType={true} />
            <Input name={`country`} label="country" value={values.country} onChange={handleChange} error={errors.country} required={true} inputType={true} />
            <Input name={`state`} label="state" value={values.state} onChange={handleChange} error={errors.state} required={true} inputType={true} />
            <Input name={`city`} label="city" value={values.city} onChange={handleChange} error={errors.city} required={true} inputType={true} />
            <Input name={`zipcode`} label="zipcode" value={values.zipcode} onChange={handleChange} error={errors.zipcode} required={true} inputType={true} />
            <div className={`col-md-4`}>
                <SelectSupplier id={`supplier_id`} label={`supplier`} value={values.supplier_id} handleChange={handleChange} error={errors.supplier_id} required={true} />
            </div>
            <Textarea name={`address`} className={`w-100`} label="address" value={values?.address} onChange={handleChange} error={errors.address} required={true} inputType={true} ></Textarea>
            <div className={`col-12`}>
                <SubmitButton className={`custom`} disable={loading} name={loading ? 'Submitting...' : 'Submit Form'} />
            </div>
        </CardForm>

    );
}

export default Add;
