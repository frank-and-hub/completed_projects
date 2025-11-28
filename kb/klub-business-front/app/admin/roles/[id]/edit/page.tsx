"use client";

import { getRoleDetails, updateRoleDetails } from '@/api/roles';
import InputText from '@/components/inputs/InputText';
import InputTextArea from '@/components/inputs/InputTextArea';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { cl } from '@/utils/console';
import { formStyle } from '@/utils/style';
import { use, useEffect } from 'react';

export default function EditUsers({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { loading, setLoading } = useAuth();
  const { handleSubmit, setFormData, SubmitBtn, resetForm } = useFormContext();
  const { id } = use(params);

  useEffect(() => {
    const fetchRole = async () => {
      setLoading(true);
      try {
        const user = await getRoleDetails(id);
        setFormData(user);
      } catch (err) {
        console.error('Failed to fetch user details:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchRole();
  }, [id]);

  return (
    <form onSubmit={(e) => { e.preventDefault(); handleSubmit({ api: updateRoleDetails, setLoading, onSuccess: () => { cl(`Successfully updated role`), resetForm(), window.location.href = `/admin/roles` } }); }} className={formStyle}>
      <h2 className={`col-span-full text-xl font-semibold mb-2`}>Role Detail</h2>
      <InputText name={`name`} label={`Name`} type={`text`} required />
      <InputTextArea name={`description`} label={`description`} required />
      <SubmitBtn name={loading ? `Updating...` : `Update`} label={`Save`} />
    </form>
  );
}
