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
      priority: '',
      assignedTo: '',
    },
  });

  const handleSubmit = (values: any) => {
    const filters: Record<string, any> = {};
    if (values.search) filters.search = values.search;
    if (values.status) filters.status = values.status;
    if (values.priority) filters.priority = values.priority;
    if (values.assignedTo) filters.assignedTo = values.assignedTo;
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
            placeholder="Search tasks..."
            leftSection={<IconSearch size={16} />}
            {...form.getInputProps('search')}
            style={{ flex: 1 }}
          />
          <Select
            placeholder="Status"
            data={[
              { value: 'pending', label: 'Pending' },
              { value: 'in_progress', label: 'In Progress' },
              { value: 'completed', label: 'Completed' },
              { value: 'cancelled', label: 'Cancelled' },
            ]}
            {...form.getInputProps('status')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Priority"
            data={[
              { value: 'low', label: 'Low' },
              { value: 'medium', label: 'Medium' },
              { value: 'high', label: 'High' },
              { value: 'urgent', label: 'Urgent' },
            ]}
            {...form.getInputProps('priority')}
            style={{ minWidth: 120 }}
          />
          <Select
            placeholder="Assigned To"
            data={[]} // This should be populated from API
            {...form.getInputProps('assignedTo')}
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
