'use client';

import { useState } from 'react';
import { useForm } from '@mantine/form';
import { Button, Stack, TextInput, Textarea, Select, Group, Title } from '@mantine/core';
import { post } from '@/utils/axios';
import { useRouter } from 'next/navigation';
import { toast } from 'react-toastify';

interface BusinessFormData {
  name: string;
  email: string;
  phone: string;
  address: string;
  description: string;
  category: string;
  website?: string;
}

export default function AddBusinessPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const form = useForm<BusinessFormData>({
    initialValues: {
      name: '',
      email: '',
      phone: '',
      address: '',
      description: '',
      category: '',
      website: '',
    },
    validate: {
      name: (value) => (!value ? 'Business name is required' : null),
      email: (value) => (!value ? 'Email is required' : /^\S+@\S+$/.test(value) ? null : 'Invalid email'),
      phone: (value) => (!value ? 'Phone is required' : null),
      address: (value) => (!value ? 'Address is required' : null),
      category: (value) => (!value ? 'Category is required' : null),
    },
  });

  const handleSubmit = async (values: BusinessFormData) => {
    setLoading(true);
    try {
      await post('v1/business', values);
      toast.success('Business created successfully!');
      router.push('/admin/business');
    } catch (error) {
      console.error('Error creating business:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Stack gap="md">
      <Title order={2}>Add New Business</Title>
      <form onSubmit={form.onSubmit(handleSubmit)}>
        <Stack gap="md">
          <Group grow>
            <TextInput
              label="Business Name"
              placeholder="Enter business name"
              required
              {...form.getInputProps('name')}
            />
            <TextInput
              label="Email"
              placeholder="Enter business email"
              type="email"
              required
              {...form.getInputProps('email')}
            />
          </Group>

          <Group grow>
            <TextInput
              label="Phone"
              placeholder="Enter phone number"
              required
              {...form.getInputProps('phone')}
            />
            <TextInput
              label="Website"
              placeholder="Enter website URL"
              {...form.getInputProps('website')}
            />
          </Group>

          <TextInput
            label="Address"
            placeholder="Enter business address"
            required
            {...form.getInputProps('address')}
          />

          <Select
            label="Category"
            placeholder="Select business category"
            data={[
              { value: 'retail', label: 'Retail' },
              { value: 'restaurant', label: 'Restaurant' },
              { value: 'service', label: 'Service' },
              { value: 'technology', label: 'Technology' },
              { value: 'healthcare', label: 'Healthcare' },
              { value: 'education', label: 'Education' },
              { value: 'other', label: 'Other' },
            ]}
            required
            {...form.getInputProps('category')}
          />

          <Textarea
            label="Description"
            placeholder="Enter business description"
            rows={4}
            {...form.getInputProps('description')}
          />

          <Group justify="flex-end">
            <Button variant="outline" onClick={() => router.back()}>
              Cancel
            </Button>
            <Button type="submit" loading={loading}>
              Create Business
            </Button>
          </Group>
        </Stack>
      </form>
    </Stack>
  );
}
