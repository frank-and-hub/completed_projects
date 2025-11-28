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
      category: '',
    },
  });

  const handleSubmit = (values: any) => {
    const filters: Record<string, any> = {};
    if (values.search) filters.search = values.search;
    if (values.status) filters.status = values.status;
    if (values.category) filters.category = values.category;
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
            placeholder="Search businesses..."
            leftSection={<IconSearch size={16} />}
            {...form.getInputProps('search')}
            style={{ flex: 1 }}
          />
          <Select
            placeholder="Status"
            data={[
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
              { value: 'pending', label: 'Pending' },
            ]}
            {...form.getInputProps('status')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Category"
            data={[
              { value: 'retail', label: 'Retail' },
              { value: 'restaurant', label: 'Restaurant' },
              { value: 'service', label: 'Service' },
              { value: 'other', label: 'Other' },
            ]}
            {...form.getInputProps('category')}
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
