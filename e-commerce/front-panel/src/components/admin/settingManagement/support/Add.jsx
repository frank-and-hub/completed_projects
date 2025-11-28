import React, { useState } from 'react'
import { post } from 'utils/AxiosUtils'
import SubmitButton from 'components/admin/form/SubmitButton'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { supportValidation, useFormValidation } from 'utils/FormValidation'
import Input from 'components/admin/form/Input'
import SelectForm from 'components/admin/form/select/SelectForm'
import { OptionsSupportType, OptionsWeeks } from 'utils/selects'
import Textarea from 'components/admin/form/Textarea'

function Add() {

  const { loading, setLoading } = useLoading();
  const [formKey, setFormKey] = useState(0);
  const navigate = useNavigate();

  const initialState = {
    cell: ``,
    type: ``,
    email: ``,
    address: ``,
    hours_start: ``,
    hours_end: ``,
    week_start: ``,
    week_end: ``
  };

  const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, supportValidation);

  const handleSubmit = async (e) => {
    e.preventDefault();
    validateSubmit(e);
    if (errors && Object.keys(errors).length > 0) {
      console.table(errors);
      return false;
    }
    setLoading(true)
    try {
      const res = await post('/support-details', values);
      if (res) {
        resetForm()
        notifySuccess(res.message)
        navigate('/admin/supports', true);
      }
    } catch (err) {
      console.error(err.message);
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
    <>
      <div className={`card`}>
        <div className={`card-body`}>
          <form key={formKey} encType={`multipart/form-data`} className={`row mt-3 g-3 needs-validation`} onSubmit={handleSubmit} noValidate>
            <Input name={`cell`} label="cell" value={values.cell} onChange={handleChange} error={errors.cell} required={true} inputType={true} />
            
            <div className={`col-md-4`}>
                <SelectForm id="type" label={`type`} value={values.type} handleChange={handleChange} error={errors.type} required={false} Options={OptionsSupportType} />
            </div>

            <Input name={`email`} label="email" value={values.email} onChange={handleChange} error={errors.email} required={true} inputType={true} />
            
            {/* <Input name={`address`} label="address" value={values.address} onChange={handleChange} error={errors.address} required={true} inputType={true} /> */}
            <Textarea name={`address`} label="address" value={values.address} className={`w-100`} onChange={handleChange} error={errors.address} inputType={true} required={true} />
            
            <Input name={`hours_start`} label="hours start" type={`time`} value={values.hours_start} onChange={handleChange} error={errors.hours_start} required={true} inputType={true} />
            <Input name={`hours_end`} label="hours end" type={`time`} value={values.hours_end} onChange={handleChange} error={errors.hours_end} required={true} inputType={true} />
            
            <div className={`col-md-4`}>
                <SelectForm id="week_start" label={`week start`} value={values.week_start} handleChange={handleChange} error={errors.week_start} required={false} Options={OptionsWeeks} />
            </div>
            <div className={`col-md-4`}>
                <SelectForm id="week_end" label={`week end`} value={values.week_end} handleChange={handleChange} error={errors.week_end} required={false} Options={OptionsWeeks} />
            </div>

            {/* <Input name={`week_start`} label="week start" type={`week`} value={values.week_start} onChange={handleChange} error={errors.week_start} required={true} inputType={true} /> */}
            {/* <Input name={`week_end`} label="week end" type={`week`} value={values.week_end} onChange={handleChange} error={errors.week_end} required={true} inputType={true} /> */}
            
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
