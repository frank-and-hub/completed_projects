'use client';

import { useState, useEffect } from 'react';
import { Grid, Paper, Text, Group, Stack, Badge, Progress, RingProgress, Center, ThemeIcon } from '@mantine/core';
import { IconUsers, IconBuilding, IconCalendar, IconChecklist, IconBell, IconMessage } from '@tabler/icons-react';
import { get } from '@/utils/axios';

interface DashboardStats {
  totalUsers: number;
  totalBusinesses: number;
  totalEmployees: number;
  totalTasks: number;
  completedTasks: number;
  upcomingEvents: number;
  unreadNotifications: number;
  unreadMessages: number;
}

export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats>({
    totalUsers: 0,
    totalBusinesses: 0,
    totalEmployees: 0,
    totalTasks: 0,
    completedTasks: 0,
    upcomingEvents: 0,
    unreadNotifications: 0,
    unreadMessages: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardStats();
  }, []);

  const fetchDashboardStats = async () => {
    try {
      // Fetch all stats in parallel
      const [users, businesses, employees, tasks, events, notifications, messages] = await Promise.all([
        get('v1/users').catch(() => ({ data: [] })),
        get('v1/business').catch(() => ({ data: [] })),
        get('v1/employee').catch(() => ({ data: [] })),
        get('v1/task').catch(() => ({ data: [] })),
        get('v1/event?upcoming=true').catch(() => ({ data: [] })),
        get('v1/notification').catch(() => ({ data: [] })),
        get('v1/chat').catch(() => ({ data: [] })),
      ]);

      const completedTasks = tasks.data?.filter((task: any) => task.status === 'completed') || [];
      const unreadNotifications = notifications.data?.filter((notif: any) => !notif.isRead) || [];
      const unreadMessages = messages.data?.filter((msg: any) => !msg.isRead) || [];

      setStats({
        totalUsers: users.data?.length || 0,
        totalBusinesses: businesses.data?.length || 0,
        totalEmployees: employees.data?.length || 0,
        totalTasks: tasks.data?.length || 0,
        completedTasks: completedTasks.length,
        upcomingEvents: events.data?.length || 0,
        unreadNotifications: unreadNotifications.length,
        unreadMessages: unreadMessages.length,
      });
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
    } finally {
      setLoading(false);
    }
  };

  const taskCompletionRate = stats.totalTasks > 0 ? (stats.completedTasks / stats.totalTasks) * 100 : 0;

  const StatCard = ({ title, value, icon, color, subtitle }: {
    title: string;
    value: number;
    icon: React.ReactNode;
    color: string;
    subtitle?: string;
  }) => (
    <Paper p="md" withBorder>
      <Group justify="space-between">
        <div>
          <Text size="sm" c="dimmed" tt="uppercase" fw={700}>
            {title}
          </Text>
          <Text size="xl" fw={700}>
            {value}
          </Text>
          {subtitle && (
            <Text size="xs" c="dimmed">
              {subtitle}
            </Text>
          )}
        </div>
        <ThemeIcon color={color} size={60} radius="md">
          {icon}
        </ThemeIcon>
      </Group>
    </Paper>
  );

  if (loading) {
    return (
      <Center h={400}>
        <Text>Loading dashboard...</Text>
      </Center>
    );
  }

  return (
    <Stack gap="md">
      <Text size="xl" fw={700}>Dashboard Overview</Text>
      
      <Grid>
        <Grid.Col span={{ base: 12, sm: 6, md: 3 }}>
          <StatCard
            title="Total Users"
            value={stats.totalUsers}
            icon={<IconUsers size={30} />}
            color="blue"
            subtitle="Registered users"
          />
        </Grid.Col>
        <Grid.Col span={{ base: 12, sm: 6, md: 3 }}>
          <StatCard
            title="Businesses"
            value={stats.totalBusinesses}
            icon={<IconBuilding size={30} />}
            color="green"
            subtitle="Active businesses"
          />
        </Grid.Col>
        <Grid.Col span={{ base: 12, sm: 6, md: 3 }}>
          <StatCard
            title="Employees"
            value={stats.totalEmployees}
            icon={<IconUsers size={30} />}
            color="orange"
            subtitle="Total employees"
          />
        </Grid.Col>
        <Grid.Col span={{ base: 12, sm: 6, md: 3 }}>
          <StatCard
            title="Upcoming Events"
            value={stats.upcomingEvents}
            icon={<IconCalendar size={30} />}
            color="purple"
            subtitle="Events this week"
          />
        </Grid.Col>
      </Grid>

      <Grid>
        <Grid.Col span={{ base: 12, md: 6 }}>
          <Paper p="md" withBorder>
            <Stack>
              <Group justify="space-between">
                <Text fw={600}>Task Completion</Text>
                <Badge color="blue" variant="light">
                  {stats.completedTasks}/{stats.totalTasks}
                </Badge>
              </Group>
              <Progress
                value={taskCompletionRate}
                size="lg"
                radius="xl"
                color="blue"
              />
              <Text size="sm" c="dimmed">
                {taskCompletionRate.toFixed(1)}% of tasks completed
              </Text>
            </Stack>
          </Paper>
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 6 }}>
          <Paper p="md" withBorder>
            <Stack>
              <Text fw={600}>Quick Actions</Text>
              <Group>
                <Badge
                  color="red"
                  variant="light"
                  leftSection={<IconBell size={12} />}
                >
                  {stats.unreadNotifications} notifications
                </Badge>
                <Badge
                  color="green"
                  variant="light"
                  leftSection={<IconMessage size={12} />}
                >
                  {stats.unreadMessages} messages
                </Badge>
              </Group>
            </Stack>
          </Paper>
        </Grid.Col>
      </Grid>

      <Grid>
        <Grid.Col span={{ base: 12, md: 4 }}>
          <Paper p="md" withBorder>
            <Center>
              <RingProgress
                size={120}
                thickness={12}
                sections={[
                  { value: taskCompletionRate, color: 'blue' }
                ]}
                label={
                  <Text size="xs" ta="center" c="dimmed">
                    Task Progress
                  </Text>
                }
              />
            </Center>
          </Paper>
        </Grid.Col>
        <Grid.Col span={{ base: 12, md: 8 }}>
          <Paper p="md" withBorder>
            <Stack>
              <Text fw={600}>Recent Activity</Text>
              <Text size="sm" c="dimmed">
                Dashboard activity will be displayed here once backend integration is complete.
              </Text>
            </Stack>
          </Paper>
        </Grid.Col>
      </Grid>
    </Stack>
  );
}