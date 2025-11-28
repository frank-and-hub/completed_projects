'use client';

import { useState } from 'react';
import { useForm } from '@mantine/form';
import { Button, Stack, TextInput, Textarea, Select, Group, Title, DateInput, NumberInput, Switch } from '@mantine/core';
import { post } from '@/utils/axios';
import { useRouter } from 'next/navigation';
import { toast } from 'react-toastify';

interface EventFormData {
  title: string;
  description: string;
  startDate: Date;
  endDate: Date;
  location: string;
  businessId: string;
  isPublic: boolean;
  maxAttendees: number;
}

export default function AddEventPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const form = useForm<EventFormData>({
    initialValues: {
      title: '',
      description: '',
      startDate: new Date(),
      endDate: new Date(),
      location: '',
      businessId: '',
      isPublic: false,
      maxAttendees: 0,
    },
    validate: {
      title: (value) => (!value ? 'Title is required' : null),
      description: (value) => (!value ? 'Description is required' : null),
      startDate: (value) => (!value ? 'Start date is required' : null),
      endDate: (value) => (!value ? 'End date is required' : null),
      location: (value) => (!value ? 'Location is required' : null),
      businessId: (value) => (!value ? 'Business is required' : null),
    },
  });

  const handleSubmit = async (values: EventFormData) => {
    setLoading(true);
    try {
      await post('v1/event', values);
      toast.success('Event created successfully!');
      router.push('/admin/events');
    } catch (error) {
      console.error('Error creating event:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Stack gap="md">
      <Title order={2}>Add New Event</Title>
      <form onSubmit={form.onSubmit(handleSubmit)}>
        <Stack gap="md">
          <TextInput
            label="Event Title"
            placeholder="Enter event title"
            required
            {...form.getInputProps('title')}
          />

          <Textarea
            label="Description"
            placeholder="Enter event description"
            rows={4}
            required
            {...form.getInputProps('description')}
          />

          <Group grow>
            <DateInput
              label="Start Date"
              placeholder="Select start date"
              required
              {...form.getInputProps('startDate')}
            />
            <DateInput
              label="End Date"
              placeholder="Select end date"
              required
              {...form.getInputProps('endDate')}
            />
          </Group>

          <TextInput
            label="Location"
            placeholder="Enter event location"
            required
            {...form.getInputProps('location')}
          />

          <Group grow>
            <Select
              label="Business"
              placeholder="Select business"
              data={[]} // This should be populated from API
              required
              {...form.getInputProps('businessId')}
            />
            <NumberInput
              label="Max Attendees"
              placeholder="Enter maximum attendees"
              min={0}
              {...form.getInputProps('maxAttendees')}
            />
          </Group>

          <Switch
            label="Public Event"
            description="Allow public access to this event"
            {...form.getInputProps('isPublic', { type: 'checkbox' })}
          />

          <Group justify="flex-end">
            <Button variant="outline" onClick={() => router.back()}>
              Cancel
            </Button>
            <Button type="submit" loading={loading}>
              Create Event
            </Button>
          </Group>
        </Stack>
      </form>
    </Stack>
  );
}
