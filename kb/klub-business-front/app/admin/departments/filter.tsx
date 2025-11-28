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
    },
  });

  const handleSubmit = (values: any) => {
    const filters: Record<string, any> = {};
    if (values.search) filters.search = values.search;
    if (values.status) filters.status = values.status;
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
            placeholder="Search departments..."
            leftSection={<IconSearch size={16} />}
            {...form.getInputProps('search')}
            style={{ flex: 1 }}
          />
          <Select
            placeholder="Status"
            data={[
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
            ]}
            {...form.getInputProps('status')}
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
