import { configureStore } from '@reduxjs/toolkit';
import authReducer from '@/store/slices/authSlice';
import userReducer from '@/store/slices/userSlice';
import roleReducer from '@/store/slices/roleSlice';
import selectReducer from '@/store/slices/selectOptionsSlice';


export const store = configureStore({
  reducer: {
    auth: authReducer,
    select: selectReducer,
    users: userReducer,
    roles: roleReducer,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;