import React, { useEffect, useState } from 'react'

import SelectIcon from 'components/admin/form/select/SelectIcon'
import SelectMenu from 'components/admin/form/select/SelectMenu'
import SubmitButton from 'components/admin/form/SubmitButton'
import Input from 'components/admin/form/Input'
import { notifySuccess } from 'components/admin/comman/notification/Notification'
import { useNavigate, useParams } from 'react-router-dom'
import { useDispatch } from 'react-redux'
import { processNotifications } from 'utils/notificationUtils'
import { formattedData } from 'utils/helper'
import { get, patch } from 'utils/AxiosUtils'
import { useLoading } from 'context/LoadingContext'
import CardForm from 'components/admin/card/CardForm'
import { menuValidation, useFormValidation } from 'utils/FormValidation'

function Edit() {
  const { id } = useParams();
  const dispatch = useDispatch();
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
      const newValues = formattedData(values);
      const res = await patch(`/menus/${id}`, newValues);
      if (res) {
        resetForm()
      }
      notifySuccess(res.message)
      navigate('/admin/menus', { replace: true })
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

  useEffect(() => {
    const fetchData = async () => {
      try {
        // Fetch roles and user data in parallel
        const [menuData] = await Promise.all([
          get(`/menus/${id}/edit`),
        ]);

        setValues(menuData?.data || {});

        processNotifications(200, menuData?.message, dispatch);
      } catch (err) {

        processNotifications(err.status || 500, err.message, dispatch);
      }
    };
    if (id) {
      fetchData();
    }
  }, [dispatch, id, setValues]);

  return (
    <CardForm handleSubmit={handleSubmit} key={formKey}>
      <Input name={`name`} label="Menu Name" value={values?.name} onChange={handleChange} error={errors.name} inputType={true} required={true} />
      <Input name={`route`} label="Route" value={values?.route} onChange={handleChange} error={errors.route} inputType={true} required={true} />

      <div className={`col-md-4`}>
        <SelectIcon id="icon" value={values?.icon} handleChange={handleChange} error={errors.icon} required={true} disabled={false} label='Icon' />
      </div>

      <div className={`col-md-4`}>
        <SelectMenu id="parent" value={values?.parent} handleChange={handleChange} error={errors.parent} required={true} disabled={false} label='Parent Menu' />
      </div>

      <div className={`col-12`}>
        <SubmitButton className={`custom`} disable={loading} name={loading ? 'Updating...' : 'Update Form'} />
      </div>
    </CardForm>
  );
}

export default Edit;
