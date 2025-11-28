'use client';
import { googleLogin } from '@/api/auth/login';
import { useAppDispatch } from '@/store/hooks';
import { updateToken } from '@/store/reducer/userReducer';
import { Loader } from '@mantine/core';
import { useMutation } from '@tanstack/react-query';
import React, { useEffect } from 'react';

const page = () => {
  const dispatch = useAppDispatch();

  const { mutate } = useMutation({
    mutationFn: googleLogin,
    onSuccess: (data) => {
      dispatch(updateToken(data?.data));

      localStorage.setItem('login-microsoft', data.data?.token);

      window.close();
    },
  });
  useEffect(() => {
    if (typeof window !== 'undefined') {
      // Parse the URL hash parameters
      const hash = window.location.hash.substring(1);
      if (hash) {
        const params = new URLSearchParams(hash);
        const accessToken = params.get('access_token');
        if (accessToken) {
          mutate({
            token: accessToken,
            social_type: 'microsoft',
          });
        }

        // You can now use the access token as needed, e.g., save it to state, make API calls, etc.
      }
    }
  }, []);

  return (
    <div style={{ minHeight: 400 }}>
      <Loader
        style={{
          position: 'absolute',
          top: '30%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
        }}
      />
    </div>
  );
};

export default page;
