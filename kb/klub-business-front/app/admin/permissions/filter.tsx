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
      resource: '',
      action: '',
    },
  });

  const handleSubmit = (values: any) => {
    const filters: Record<string, any> = {};
    if (values.search) filters.search = values.search;
    if (values.resource) filters.resource = values.resource;
    if (values.action) filters.action = values.action;
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
            placeholder="Search permissions..."
            leftSection={<IconSearch size={16} />}
            {...form.getInputProps('search')}
            style={{ flex: 1 }}
          />
          <Select
            placeholder="Resource"
            data={[
              { value: 'users', label: 'Users' },
              { value: 'business', label: 'Business' },
              { value: 'employee', label: 'Employee' },
              { value: 'task', label: 'Task' },
              { value: 'event', label: 'Event' },
            ]}
            {...form.getInputProps('resource')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Action"
            data={[
              { value: 'create', label: 'Create' },
              { value: 'read', label: 'Read' },
              { value: 'update', label: 'Update' },
              { value: 'delete', label: 'Delete' },
            ]}
            {...form.getInputProps('action')}
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
