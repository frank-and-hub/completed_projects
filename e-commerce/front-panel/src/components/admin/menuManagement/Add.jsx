import React, { useState } from 'react'

import { post } from 'utils/AxiosUtils'
import SelectIcon from 'components/admin/form/select/SelectIcon'
import SelectMenu from 'components/admin/form/select/SelectMenu'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { useNavigate } from 'react-router-dom'
import { useLoading } from 'context/LoadingContext'
import { menuValidation, useFormValidation } from 'utils/FormValidation'

function Add() {

  const { loading, setLoading } = useLoading();
  const [formKey, setFormKey] = useState(0);
  const navigate = useNavigate();

  const initialState = {
    name: '',
    route: '',
    icon: '',
    parent: ''
  };

  const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, menuValidation);

  const handleSubmit = async (e) => {
    e.preventDefault();
    validateSubmit(e);
    if (errors && Object.keys(errors).length > 0) {
      console.table(errors);
      return false;
    }
    setLoading(true)
    try {
      const res = await post('/menus', values);
      if (res) {
        resetForm()
        navigate('/admin/menus', true);
      }
      notifySuccess(res.message)
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

            <Input name={`name`} label="Menu Name" value={values.name} onChange={handleChange} error={errors.name} inputType={true} required={true} />
            <Input name={`route`} label="Route" value={values.route} onChange={handleChange} error={errors.route} inputType={true} required={true} />

            <div className={`col-md-4`}>
              <SelectIcon id="icon" label={`Icon`} value={values.icon} handleChange={handleChange} error={errors.icon} required={true}/>
            </div>

            <div className={`col-md-4`}>
              <SelectMenu id="parent" label={`parent Menu`} value={values.parent} handleChange={handleChange} error={errors.parent} required={false} disabled={false} />
            </div>

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
