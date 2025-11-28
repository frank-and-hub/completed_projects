'use client';

import { useState, useEffect } from 'react';
import { useForm } from '@mantine/form';
import { Button, Stack, TextInput, Textarea, Select, Group, Title, LoadingOverlay } from '@mantine/core';
import { get, put } from '@/utils/axios';
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

export default function EditBusinessPage({ params }: { params: { id: string } }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);

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

  useEffect(() => {
    const fetchBusiness = async () => {
      try {
        const business = await get(`v1/business/${params.id}`);
        form.setValues({
          name: business.name || '',
          email: business.email || '',
          phone: business.phone || '',
          address: business.address || '',
          description: business.description || '',
          category: business.category || '',
          website: business.website || '',
        });
      } catch (error) {
        console.error('Error fetching business:', error);
        toast.error('Failed to load business data');
      } finally {
        setInitialLoading(false);
      }
    };

    fetchBusiness();
  }, [params.id]);

  const handleSubmit = async (values: BusinessFormData) => {
    setLoading(true);
    try {
      await put(`business/${params.id}`, values);
      toast.success('Business updated successfully!');
      router.push('/admin/business');
    } catch (error) {
      console.error('Error updating business:', error);
    } finally {
      setLoading(false);
    }
  };

  if (initialLoading) {
    return <LoadingOverlay visible />;
  }

  return (
    <Stack gap="md">
      <Title order={2}>Edit Business</Title>
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
              Update Business
            </Button>
          </Group>
        </Stack>
      </form>
    </Stack>
  );
}
