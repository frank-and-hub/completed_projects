'use client';

import { useState } from 'react';
import { useForm } from '@mantine/form';
import { Button, Stack, TextInput, Textarea, Select, Group, Title, DateInput, NumberInput } from '@mantine/core';
import { post } from '@/utils/axios';
import { useRouter } from 'next/navigation';
import { toast } from 'react-toastify';

interface TaskFormData {
  title: string;
  description: string;
  assignedTo: string;
  assignedBy: string;
  businessId: string;
  priority: string;
  dueDate: Date;
  estimatedHours: number;
}

export default function AddTaskPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const form = useForm<TaskFormData>({
    initialValues: {
      title: '',
      description: '',
      assignedTo: '',
      assignedBy: '',
      businessId: '',
      priority: '',
      dueDate: new Date(),
      estimatedHours: 0,
    },
    validate: {
      title: (value) => (!value ? 'Title is required' : null),
      description: (value) => (!value ? 'Description is required' : null),
      assignedTo: (value) => (!value ? 'Assigned to is required' : null),
      assignedBy: (value) => (!value ? 'Assigned by is required' : null),
      businessId: (value) => (!value ? 'Business is required' : null),
      priority: (value) => (!value ? 'Priority is required' : null),
    },
  });

  const handleSubmit = async (values: TaskFormData) => {
    setLoading(true);
    try {
      await post('v1/task', values);
      toast.success('Task created successfully!');
      router.push('/admin/tasks');
    } catch (error) {
      console.error('Error creating task:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Stack gap="md">
      <Title order={2}>Add New Task</Title>
      <form onSubmit={form.onSubmit(handleSubmit)}>
        <Stack gap="md">
          <TextInput
            label="Task Title"
            placeholder="Enter task title"
            required
            {...form.getInputProps('title')}
          />

          <Textarea
            label="Description"
            placeholder="Enter task description"
            rows={4}
            required
            {...form.getInputProps('description')}
          />

          <Group grow>
            <Select
              label="Assigned To"
              placeholder="Select employee"
              data={[]} // This should be populated from API
              required
              {...form.getInputProps('assignedTo')}
            />
            <Select
              label="Assigned By"
              placeholder="Select assigner"
              data={[]} // This should be populated from API
              required
              {...form.getInputProps('assignedBy')}
            />
          </Group>

          <Group grow>
            <Select
              label="Business"
              placeholder="Select business"
              data={[]} // This should be populated from API
              required
              {...form.getInputProps('businessId')}
            />
            <Select
              label="Priority"
              placeholder="Select priority"
              data={[
                { value: 'low', label: 'Low' },
                { value: 'medium', label: 'Medium' },
                { value: 'high', label: 'High' },
                { value: 'urgent', label: 'Urgent' },
              ]}
              required
              {...form.getInputProps('priority')}
            />
          </Group>

          <Group grow>
            <DateInput
              label="Due Date"
              placeholder="Select due date"
              required
              {...form.getInputProps('dueDate')}
            />
            <NumberInput
              label="Estimated Hours"
              placeholder="Enter estimated hours"
              min={0}
              {...form.getInputProps('estimatedHours')}
            />
          </Group>

          <Group justify="flex-end">
            <Button variant="outline" onClick={() => router.back()}>
              Cancel
            </Button>
            <Button type="submit" loading={loading}>
              Create Task
            </Button>
          </Group>
        </Stack>
      </form>
    </Stack>
  );
}
