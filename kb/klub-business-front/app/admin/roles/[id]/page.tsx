"use client";

import { getRoleDetails } from '@/api/roles';
import InputText from '@/components/inputs/InputText';
import InputTextArea from '@/components/inputs/InputTextArea';
import { useAuth } from '@/context/AuthContext';
import { useFormContext } from '@/context/FormContext';
import { formStyle } from '@/utils/style';
import { use, useEffect } from 'react';

export default function ViewRoles({
    params,
}: {
    params: Promise<{ id: string }>;
}) {
    const { setFormData } = useFormContext();
    const { setLoading } = useAuth();
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
        <form className={formStyle}>
            <h2 className={`col-span-full text-xl font-semibold mb-2`}>Role Detail</h2>
            <InputText name={`name`} label={`Name`} type={`text`} readOnly />
            <InputTextArea name={`description`} label={`description`} readOnly />
        </form>
    );
}
