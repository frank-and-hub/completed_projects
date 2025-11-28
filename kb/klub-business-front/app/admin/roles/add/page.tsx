'use client';

import { createNewRole } from '@/api/roles';
import InputText from '@/components/inputs/InputText';
import InputTextArea from '@/components/inputs/InputTextArea';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { cc } from '@/utils/console';
import { useRouter } from 'next/navigation';

export default function AddRole() {
    const router = useRouter();
    const { loading, setLoading } = useAuth();
    const { handleSubmit, SubmitBtn } = useFormContext();

    return (
        <form onSubmit={(e) => {
            e.preventDefault();
            handleSubmit({
                api: createNewRole,
                setLoading,
                onSuccess: () => {cc(`Successfully created new role`), router.push(`/admin/roles`)}
            });
        }} className={`grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-w-7xl mx-auto px-0 py-3 sm:py-5 sm:px-5`}>
            <h2 className={`col-span-full text-xl font-semibold mb-2`}>Add New Role</h2>
            <InputText name={`name`} label={`name`} type={`text`} required />
            <InputTextArea name={`description`} label={`description`} required />
            <SubmitBtn name={loading ? `Submiting...` : `Submit`} label={`Save`} />
        </form>
    );
}
