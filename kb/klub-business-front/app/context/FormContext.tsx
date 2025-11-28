'use client';

import CustomButton from '@/components/buttons/Button';
import { sanitizeFormData, sleep } from '@/utils/helpers';
import { Box, Group, LoadingOverlay } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import React, { createContext, useContext, useState } from 'react';

interface FormContextProps {
  formData: Record<string, any>;
  errors: Record<string, string>;
  handleChange: (e: { target: { name: string; value: string | null } }) => void;
  setFormData: React.Dispatch<React.SetStateAction<Record<string, any>>>;
  setErrors: React.Dispatch<React.SetStateAction<Record<string, string>>>;
  handleSubmit: (props: handleSubmitProps) => Promise<void>;
  SubmitBtn: ({ name, label }: { name: string; label: string }) => React.JSX.Element;
  resetForm: () => void;
}

interface handleSubmitProps {
  api: (data: any, id?: string | undefined | null | any) => Promise<any>;
  setLoading: (loading: boolean) => void;
  onSuccess?: () => void;
}

const FormContext = createContext<FormContextProps | undefined>(undefined);

export const useFormContext = () => {
  const context = useContext(FormContext);
  if (!context) throw new Error('useFormContext must be used within a FormProvider');
  return context;
};

export const FormProvider = ({ children }: { children: React.ReactNode }) => {
  const [formData, setFormData] = useState<Record<string, any>>({});
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [visible, { open, close }] = useDisclosure(false);
  const newErrors: Record<string, string> = {};

  const handleChange = (e: { target: { name: string; value: string | null } }) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    setErrors((prev) => ({ ...prev, [name]: '' })); // Clear error on change
  };

  const SubmitBtn = ({ name = 'Submit', label = 'Save' }: { name: string, label: string }) => {
    return (
      <Group className={`col-span-full flex justify-end mt-4`}>
        <CustomButton type={'submit'} label={label} name={name} />
      </Group>
    );
  }

  const handleSubmit = async ({ api, setLoading, onSuccess }: handleSubmitProps) => {
    setLoading(true);
    open();
    const filteredFormData = sanitizeFormData(formData);
    try {
      const { id, ...dataToSend } = filteredFormData;
      const res = id ? await api(dataToSend, id) : await api(dataToSend);
      if (res) {
        onSuccess?.();
        resetForm();
        close();
        setLoading(false);
      }
    } catch (err: any) {
      const res = (err?.message ?? (err?.response ?? (err?.data ?? [])));
      const statusCode = err.statusCode ?? (err.message.statusCode ?? (err.request.status ?? (err.response.statusCode)));
      const backendErrors = (err?.message ?? (err.message.message ?? (res?.data.message.message ?? [])));

      if ((statusCode == 400) && Array.isArray(backendErrors)) {
        backendErrors.forEach((msg: string) => {
          const field = msg.split(' ')[0];
          newErrors[field] = msg;
        });
        setErrors(newErrors);
      } else {
        console.error('Unexpected error:', err);
        close();
      }
    }
  };

  const resetForm = () => {
    setFormData({});
    setErrors({});
  };

  return (
    <FormContext.Provider value={{ formData, errors, handleChange, setFormData, setErrors, handleSubmit, SubmitBtn, resetForm }} >
      <Box pos={`relative`}>
        <LoadingOverlay visible={visible} loaderProps={{ children: 'Loading...' }} />
        {children}
      </Box>
    </FormContext.Provider>
  );
};
