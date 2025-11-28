'use client';

import CustomButton from '@/components/buttons/Button';
import { Group, TextInput, Select, Stack } from '@mantine/core';
import { useForm } from '@mantine/form';
import { IconSearch, IconFilter } from '@tabler/icons-react';

interface FilterProps {
  setFilters: (filters: Record<string, any>) => void;
  visible: boolean;
}

export default function UseFilter({ setFilters, visible }: FilterProps) {
  const form = useForm({
    initialValues: {
      search: '',
      status: '',
      department: '',
      position: '',
    },
  });

  const handleSubmit = (values: any) => {
    const filters: Record<string, any> = {};
    if (values.search) filters.search = values.search;
    if (values.status) filters.status = values.status;
    if (values.department) filters.department = values.department;
    if (values.position) filters.position = values.position;
    setFilters(filters);
  };

  const handleClear = () => {
    form.reset();
    setFilters({});
  };

  if (!visible) return null;

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <Group>
          <TextInput
            placeholder="Search employees..."
            leftSection={<IconSearch size={16} />}
            {...form.getInputProps('search')}
            style={{ flex: 1 }}
          />
          <Select
            placeholder="Status"
            data={[
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
              { value: 'on_leave', label: 'On Leave' },
            ]}
            {...form.getInputProps('status')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Department"
            data={[
              { value: 'hr', label: 'Human Resources' },
              { value: 'it', label: 'Information Technology' },
              { value: 'finance', label: 'Finance' },
              { value: 'marketing', label: 'Marketing' },
              { value: 'sales', label: 'Sales' },
            ]}
            {...form.getInputProps('department')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Position"
            data={[
              { value: 'manager', label: 'Manager' },
              { value: 'developer', label: 'Developer' },
              { value: 'analyst', label: 'Analyst' },
              { value: 'assistant', label: 'Assistant' },
            ]}
            {...form.getInputProps('position')}
            style={{ minWidth: 120 }}
          />
        </Group>
        <Group>
          <CustomButton type="submit" name={`Apply Filters`} leftSection={<IconFilter size={16} />} />
          <CustomButton variant="outline" onClick={handleClear} name={`Clear`} />
        </Group>
      </Stack>
    </form>
  );
}
