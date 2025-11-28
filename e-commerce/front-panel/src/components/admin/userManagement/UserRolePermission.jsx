import React, { useContext, useState } from 'react'
import SubmitButton from 'components/admin/form/SubmitButton'
import { notifyInfo } from 'components/admin/comman/notification/Notification'
import { get } from 'utils/AxiosUtils'
import { useLoading } from 'context/LoadingContext'
import PermissionTable from './PermissionTable'
import { SidebarContext } from 'context/SidebarContext'
import SelectUser from 'components/admin/form/select/SelectUser'
import { useFormValidation, userPermissionValidation } from 'utils/FormValidation'

function UserRolePermission() {

  const [userResponse, setUserResponse] = useState(false);
  const [showPermission, setShowPermission] = useState(null);
  const [formKey, setFormKey] = useState(0);
  const { loading, setLoading } = useLoading();
  const { menus } = useContext(SidebarContext);

  const initialState = {
    user_id: ''
  };

  const { formData: values, errors, handleChange, handleSubmit: validateSubmit, setFormData: setValues } = useFormValidation(initialState, userPermissionValidation);

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
      const res = await get(`/users/${values.user_id}/permssions`);
      setUserResponse(res?.data);
      setShowPermission(true);
    } catch (err) {
      console.error(err.message);
    } finally {
      setLoading(false)
    }
  };

  const resetForm = () => {
    setValues(initialState);
    setShowPermission(false);
    setFormKey((prevKey) => prevKey + 1);
    document.getElementsByTagName('form')[0].reset();
  };

  const arrayPermissions = ['view', 'add', 'edit', 'delete'];

  const TableData = {
    response: menus?.data,
    permission: userResponse,
    user_id: values?.user_id,
    mainFormReset: resetForm,
    permissionsName: arrayPermissions
  }

  return (
    <>
      <div className={`card`}>
        <div className={`card-body`}>
          <form key={formKey} encType={`multipart/form-data`} className={`row mt-3 g-3 needs-validation`} onSubmit={handleSubmit} noValidate>
            <div className={`col-md-4`}>
              <SelectUser id={`user_id`} value={values?.user_id} handleChange={(e) => { handleChange(e); }} error={errors.user_id} required={true} disabled={false} label='User' />
            </div>
            <div className={`col-md-12`}>
              <SubmitButton className={`custom`} name={loading ? 'Loading...' : 'Apply Filter'} />
              <SubmitButton className={`secondary`} name={`Reset`} type='button' onClick={resetForm} />
            </div>
          </form>
        </div>
      </div>
      {showPermission && (<PermissionTable {...TableData} />)}
    </>
  )
}

export default UserRolePermission