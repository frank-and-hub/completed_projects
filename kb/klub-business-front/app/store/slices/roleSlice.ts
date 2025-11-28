import { createSlice, PayloadAction } from '@reduxjs/toolkit';

interface Role {
  id: number;
  name: string;
}

interface RoleState {
  list: Role[];
  loading: boolean;
}

const initialState: RoleState = {
  list: [],
  loading: false,
};

const roleSlice = createSlice({
  name: 'role',
  initialState,
  reducers: {
    setRoles(state, action: PayloadAction<Role[]>) {
      state.list = action.payload;
    },
    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },
  },
});

export const { setRoles, setLoading } = roleSlice.actions;
export default roleSlice.reducer;
