'use client';

import { useState, useEffect, useRef } from 'react';
import { Stack, TextInput, Button, ScrollArea, Paper, Group, Avatar, Text, ActionIcon } from '@mantine/core';
import { IconSend, IconPaperclip, IconPhone, IconVideo } from '@tabler/icons-react';
import { get, post } from '@/utils/axios';

interface Message {
  id: string;
  content: string;
  senderId: string;
  senderName: string;
  timestamp: string;
  type: 'text' | 'file' | 'image';
}

interface Chat {
  id: string;
  name: string;
  participants: any[];
  lastMessage?: Message;
  unreadCount: number;
}

export default function ChatPage() {
  const [chats, setChats] = useState<Chat[]>([]);
  const [selectedChat, setSelectedChat] = useState<string | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetchChats();
  }, []);

  useEffect(() => {
    if (selectedChat) {
      fetchMessages(selectedChat);
    }
  }, [selectedChat]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const fetchChats = async () => {
    try {
      const data = await get('v1/chat');
      setChats(data);
    } catch (error) {
      console.error('Error fetching chats:', error);
    }
  };

  const fetchMessages = async (chatId: string) => {
    try {
      const data = await get(`v1/chat/${chatId}/messages`);
      setMessages(data);
    } catch (error) {
      console.error('Error fetching messages:', error);
    }
  };

  const sendMessage = async () => {
    if (!newMessage.trim() || !selectedChat) return;

    setLoading(true);
    try {
      await post(`v1/chat/${selectedChat}/messages`, {
        content: newMessage,
        type: 'text',
      });
      setNewMessage('');
      fetchMessages(selectedChat);
    } catch (error) {
      console.error('Error sending message:', error);
    } finally {
      setLoading(false);
    }
  };

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  return (
    <div style={{ display: 'flex', height: 'calc(100vh - 200px)' }}>
      {/* Chat List */}
      <div style={{ width: '300px', borderRight: '1px solid #e0e0e0' }}>
        <Stack gap="xs" p="md">
          <Text size="lg" fw={600}>Chats</Text>
          {chats.map((chat) => (
            <Paper
              key={chat.id}
              p="md"
              style={{
                cursor: 'pointer',
                backgroundColor: selectedChat === chat.id ? '#f0f0f0' : 'transparent',
              }}
              onClick={() => setSelectedChat(chat.id)}
            >
              <Group>
                <Avatar size="sm" />
                <div style={{ flex: 1 }}>
                  <Text size="sm" fw={500}>{chat.name}</Text>
                  {chat.lastMessage && (
                    <Text size="xs" c="dimmed" truncate>
                      {chat.lastMessage.content}
                    </Text>
                  )}
                </div>
                {chat.unreadCount > 0 && (
                  <Text size="xs" c="blue" fw={600}>
                    {chat.unreadCount}
                  </Text>
                )}
              </Group>
            </Paper>
          ))}
        </Stack>
      </div>

      {/* Chat Area */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
        {selectedChat ? (
          <>
            {/* Chat Header */}
            <Paper p="md" style={{ borderBottom: '1px solid #e0e0e0' }}>
              <Group justify="space-between">
                <Group>
                  <Avatar size="sm" />
                  <div>
                    <Text fw={500}>Chat Name</Text>
                    <Text size="xs" c="dimmed">Online</Text>
                  </div>
                </Group>
                <Group>
                  <ActionIcon variant="subtle">
                    <IconPhone size={16} />
                  </ActionIcon>
                  <ActionIcon variant="subtle">
                    <IconVideo size={16} />
                  </ActionIcon>
                </Group>
              </Group>
            </Paper>

            {/* Messages */}
            <ScrollArea style={{ flex: 1 }} p="md">
              <Stack gap="md">
                {messages.map((message) => (
                  <div
                    key={message.id}
                    style={{
                      display: 'flex',
                      justifyContent: message.senderId === 'current-user' ? 'flex-end' : 'flex-start',
                    }}
                  >
                    <Paper
                      p="sm"
                      style={{
                        maxWidth: '70%',
                        backgroundColor: message.senderId === 'current-user' ? '#007bff' : '#f0f0f0',
                        color: message.senderId === 'current-user' ? 'white' : 'black',
                      }}
                    >
                      <Text size="sm">{message.content}</Text>
                      <Text size="xs" c="dimmed" mt={4}>
                        {new Date(message.timestamp).toLocaleTimeString()}
                      </Text>
                    </Paper>
                  </div>
                ))}
                <div ref={messagesEndRef} />
              </Stack>
            </ScrollArea>

            {/* Message Input */}
            <Paper p="md" style={{ borderTop: '1px solid #e0e0e0' }}>
              <Group>
                <ActionIcon variant="subtle">
                  <IconPaperclip size={16} />
                </ActionIcon>
                <TextInput
                  placeholder="Type a message..."
                  value={newMessage}
                  onChange={(e) => setNewMessage(e.target.value)}
                  onKeyPress={handleKeyPress}
                  style={{ flex: 1 }}
                />
                <Button
                  onClick={sendMessage}
                  loading={loading}
                  disabled={!newMessage.trim()}
                  leftSection={<IconSend size={16} />}
                >
                  Send
                </Button>
              </Group>
            </Paper>
          </>
        ) : (
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%' }}>
            <Text c="dimmed">Select a chat to start messaging</Text>
          </div>
        )}
      </div>
    </div>
  );
}
