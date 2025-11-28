'use client';

import { updateDetails, getUserDetails } from '@/api/users';
import InputText from '@/components/inputs/InputText';
import CommonSelect from '@/components/selects/CommonSelect';
import StaticSelect from '@/components/selects/StaticSelect';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { cl } from '@/utils/console';
import { get18YearsAgoDate } from '@/utils/helpers';
import { formStyle } from '@/utils/style';
import { use, useEffect } from 'react';

export default function EditUsers({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { loading, setLoading, setPageTitle } = useAuth();
  const { handleSubmit, SubmitBtn, setFormData, resetForm } = useFormContext();
  const { id } = use(params);

  useEffect(() => {
    resetForm();
    const fetchUser = async () => {
      setLoading(true);
      try {
        const user = await getUserDetails(id);
        setFormData(user);
        setPageTitle(`${user.firstName ?? ``} ${user?.lastName ?? ``}`);
      } catch (err) {
        console.error('Failed to fetch user details:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchUser();
  }, [id]);

  return (
    <form onSubmit={(e) => {
      e.preventDefault();
      handleSubmit({
        api: updateDetails,
        setLoading,
        onSuccess: () => { cl(`Successfully created new user`), resetForm(), window.location.href= 'admin/users' }
      });
    }} className={formStyle}>
      <InputText name={`firstName`} label={`First Name`} type={`text`} required />
      <InputText name={`middleName`} label={`Middle Name`} type={`text`} />
      <InputText name={`lastName`} label={`Last Name`} type={`text`} />
      <InputText name={`email`} label={`Email`} type={`email`} required />
      <CommonSelect id={`roleId`} label={`role`} apiUrl={`/v1/common-data?type=role`} />
      <InputText name={`phone`} label={`Phone`} type={`tel`} required maxLength={12} minLength={9} />
      <InputText name={`dateOfBirth`} label={`Date Of Birth`} type={`date`} max={get18YearsAgoDate()} />
      {/* <CommonSelect id={`countryId`} label={`country`} apiUrl={`/v1/common-data?type=country`} />
      <CommonSelect id={`stateId`} label={`state`} apiUrl={`/v1/common-data?type=state`} />
      <CommonSelect id={`cityId`} label={`city`} apiUrl={`/v1/common-data?type=city`} /> */}
      {/* <StaticSelect name={`department`} label={`department`} required/> */}
      <StaticSelect name={`gender`} label='Gender' required />
      <StaticSelect name={`deviceType`} label='Device Type' />
      <StaticSelect name={`relationshipStatus`} label='Relationship Status' />
      <SubmitBtn name={loading ? `Updating...` : `Update`} label={`Save`} />
    </form>
  );
}
