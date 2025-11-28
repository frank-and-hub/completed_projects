'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { getToken } from '@/utils/useAuth';

const TAB_ID = `${Date.now()}`;

export default function SingleTabGuard() {
  const router = useRouter();
  const sessionKey = getToken();

  useEffect(() => {
    const handleStorage = (event: StorageEvent) => {
      if (event.key === sessionKey && event.newValue !== TAB_ID) {
        alert('Another tab is already active. Redirecting...');
        router.push('/auth/sign-in'); // Or logout, etc.
      }
    };

    window.addEventListener('storage', handleStorage);
    return () => {
      window.removeEventListener('storage', handleStorage);
    };
  }, [sessionKey]);

  return null; // No UI
}
