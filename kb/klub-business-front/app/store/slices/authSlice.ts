import { createSlice, PayloadAction } from '@reduxjs/toolkit';

interface AuthState {
  token: string | null;
}

const initialState: AuthState = {
  token: typeof window !== 'undefined' ? localStorage.getItem('token') : null,
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    logedIn: (state, action: PayloadAction<string>) => {
      state.token = action.payload;
      if (typeof window !== 'undefined') {
        localStorage.setItem('token', action.payload);
      }
    },
    logedOut: (state) => {
      state.token = null;
      if (typeof window !== 'undefined') {
        localStorage.removeItem('token');
      }
    },
    setTokenFromStorage: (state, action: PayloadAction<string | null>) => {
      state.token = action.payload;
    }
  },
});

export const { logedIn, logedOut, setTokenFromStorage } = authSlice.actions;
export default authSlice.reducer;
