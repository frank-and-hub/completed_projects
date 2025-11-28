'use client';

import { useState, useEffect } from 'react';
import { Stack, Paper, Group, Text, Badge, Button, ActionIcon, Switch } from '@mantine/core';
import { IconBell, IconCheck, IconTrash, IconSettings } from '@tabler/icons-react';
import { get, patch, destroy } from '@/utils/axios';
import { toast } from 'react-toastify';

interface Notification {
  id: string;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  isRead: boolean;
  createdAt: string;
  userId: string;
}

export default function NotificationsPage() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(false);
  const [filter, setFilter] = useState<'all' | 'unread' | 'read'>('all');

  useEffect(() => {
    fetchNotifications();
  }, []);

  const fetchNotifications = async () => {
    setLoading(true);
    try {
      const data = await get('v1/notification');
      setNotifications(data);
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  const markAsRead = async (id: string) => {
    try {
      await patch(`notification/${id}/read`, {});
      setNotifications(prev => 
        prev.map(notif => 
          notif.id === id ? { ...notif, isRead: true } : notif
        )
      );
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  const markAllAsRead = async () => {
    try {
      await patch('notification/mark-all-read', {});
      setNotifications(prev => 
        prev.map(notif => ({ ...notif, isRead: true }))
      );
      toast.success('All notifications marked as read');
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
    }
  };

  const deleteNotification = async (id: string) => {
    try {
      await destroy(`notification/${id}`);
      setNotifications(prev => prev.filter(notif => notif.id !== id));
      toast.success('Notification deleted');
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  };

  const getNotificationColor = (type: string) => {
    switch (type) {
      case 'success': return 'green';
      case 'warning': return 'yellow';
      case 'error': return 'red';
      default: return 'blue';
    }
  };

  const filteredNotifications = notifications.filter(notif => {
    if (filter === 'unread') return !notif.isRead;
    if (filter === 'read') return notif.isRead;
    return true;
  });

  const unreadCount = notifications.filter(notif => !notif.isRead).length;

  return (
    <Stack gap="md">
      <Group justify="space-between">
        <Group>
          <IconBell size={24} />
          <Text size="xl" fw={600}>Notifications</Text>
          {unreadCount > 0 && (
            <Badge color="red" size="lg">
              {unreadCount}
            </Badge>
          )}
        </Group>
        <Group>
          <Button
            variant="outline"
            size="sm"
            onClick={markAllAsRead}
            disabled={unreadCount === 0}
          >
            Mark All as Read
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={fetchNotifications}
            loading={loading}
          >
            Refresh
          </Button>
        </Group>
      </Group>

      <Group>
        <Button
          variant={filter === 'all' ? 'filled' : 'outline'}
          size="sm"
          onClick={() => setFilter('all')}
        >
          All ({notifications.length})
        </Button>
        <Button
          variant={filter === 'unread' ? 'filled' : 'outline'}
          size="sm"
          onClick={() => setFilter('unread')}
        >
          Unread ({unreadCount})
        </Button>
        <Button
          variant={filter === 'read' ? 'filled' : 'outline'}
          size="sm"
          onClick={() => setFilter('read')}
        >
          Read ({notifications.length - unreadCount})
        </Button>
      </Group>

      <Stack gap="sm">
        {filteredNotifications.length === 0 ? (
          <Paper p="xl" style={{ textAlign: 'center' }}>
            <Text c="dimmed">No notifications found</Text>
          </Paper>
        ) : (
          filteredNotifications.map((notification) => (
            <Paper
              key={notification.id}
              p="md"
              style={{
                borderLeft: `4px solid ${
                  notification.isRead ? '#e0e0e0' : 
                  getNotificationColor(notification.type) === 'green' ? '#4caf50' :
                  getNotificationColor(notification.type) === 'yellow' ? '#ff9800' :
                  getNotificationColor(notification.type) === 'red' ? '#f44336' : '#2196f3'
                }`,
                backgroundColor: notification.isRead ? '#f9f9f9' : 'white',
              }}
            >
              <Group justify="space-between">
                <div style={{ flex: 1 }}>
                  <Group>
                    <Text fw={notification.isRead ? 400 : 600} size="sm">
                      {notification.title}
                    </Text>
                    {!notification.isRead && (
                      <Badge size="xs" color="blue">New</Badge>
                    )}
                  </Group>
                  <Text size="sm" c="dimmed" mt={4}>
                    {notification.message}
                  </Text>
                  <Text size="xs" c="dimmed" mt={4}>
                    {new Date(notification.createdAt).toLocaleString()}
                  </Text>
                </div>
                <Group>
                  {!notification.isRead && (
                    <ActionIcon
                      variant="subtle"
                      color="blue"
                      onClick={() => markAsRead(notification.id)}
                    >
                      <IconCheck size={16} />
                    </ActionIcon>
                  )}
                  <ActionIcon
                    variant="subtle"
                    color="red"
                    onClick={() => deleteNotification(notification.id)}
                  >
                    <IconTrash size={16} />
                  </ActionIcon>
                </Group>
              </Group>
            </Paper>
          ))
        )}
      </Stack>
    </Stack>
  );
}
