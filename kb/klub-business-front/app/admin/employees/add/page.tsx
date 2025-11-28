'use client';

import { useState } from 'react';
import { useForm } from '@mantine/form';
import { Button, Stack, TextInput, Select, Group, Title, NumberInput, DateInput } from '@mantine/core';
import { post } from '@/utils/axios';
import { useRouter } from 'next/navigation';
import { toast } from 'react-toastify';

interface EmployeeFormData {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  position: string;
  department: string;
  salary: number;
  hireDate: Date;
  businessId: string;
  userId: string;
}

export default function AddEmployeePage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const form = useForm<EmployeeFormData>({
    initialValues: {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      position: '',
      department: '',
      salary: 0,
      hireDate: new Date(),
      businessId: '',
      userId: '',
    },
    validate: {
      firstName: (value) => (!value ? 'First name is required' : null),
      lastName: (value) => (!value ? 'Last name is required' : null),
      email: (value) => (!value ? 'Email is required' : /^\S+@\S+$/.test(value) ? null : 'Invalid email'),
      phone: (value) => (!value ? 'Phone is required' : null),
      position: (value) => (!value ? 'Position is required' : null),
      department: (value) => (!value ? 'Department is required' : null),
      businessId: (value) => (!value ? 'Business is required' : null),
      userId: (value) => (!value ? 'User is required' : null),
    },
  });

  const handleSubmit = async (values: EmployeeFormData) => {
    setLoading(true);
    try {
      await post('v1/employee', values);
      toast.success('Employee created successfully!');
      router.push('/admin/employees');
    } catch (error) {
      console.error('Error creating employee:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Stack gap="md">
      <Title order={2}>Add New Employee</Title>
      <form onSubmit={form.onSubmit(handleSubmit)}>
        <Stack gap="md">
          <Group grow>
            <TextInput
              label="First Name"
              placeholder="Enter first name"
              required
              {...form.getInputProps('firstName')}
            />
            <TextInput
              label="Last Name"
              placeholder="Enter last name"
              required
              {...form.getInputProps('lastName')}
            />
          </Group>

          <Group grow>
            <TextInput
              label="Email"
              placeholder="Enter email address"
              type="email"
              required
              {...form.getInputProps('email')}
            />
            <TextInput
              label="Phone"
              placeholder="Enter phone number"
              required
              {...form.getInputProps('phone')}
            />
          </Group>

          <Group grow>
            <Select
              label="Position"
              placeholder="Select position"
              data={[
                { value: 'manager', label: 'Manager' },
                { value: 'developer', label: 'Developer' },
                { value: 'analyst', label: 'Analyst' },
                { value: 'assistant', label: 'Assistant' },
                { value: 'director', label: 'Director' },
                { value: 'coordinator', label: 'Coordinator' },
              ]}
              required
              {...form.getInputProps('position')}
            />
            <Select
              label="Department"
              placeholder="Select department"
              data={[
                { value: 'hr', label: 'Human Resources' },
                { value: 'it', label: 'Information Technology' },
                { value: 'finance', label: 'Finance' },
                { value: 'marketing', label: 'Marketing' },
                { value: 'sales', label: 'Sales' },
                { value: 'operations', label: 'Operations' },
              ]}
              required
              {...form.getInputProps('department')}
            />
          </Group>

          <Group grow>
            <NumberInput
              label="Salary"
              placeholder="Enter salary"
              min={0}
              prefix="$"
              {...form.getInputProps('salary')}
            />
            <DateInput
              label="Hire Date"
              placeholder="Select hire date"
              required
              {...form.getInputProps('hireDate')}
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
              label="User"
              placeholder="Select user"
              data={[]} // This should be populated from API
              required
              {...form.getInputProps('userId')}
            />
          </Group>

          <Group justify="flex-end">
            <Button variant="outline" onClick={() => router.back()}>
              Cancel
            </Button>
            <Button type="submit" loading={loading}>
              Create Employee
            </Button>
          </Group>
        </Stack>
      </form>
    </Stack>
  );
}
