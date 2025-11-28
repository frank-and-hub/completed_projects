import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    permission: null
};

const permissionSlice = createSlice({
    name: 'permission',
    initialState,
    reducers: {
        setPermission: (state, action) => {
            state.permission = action.payload.permission;
        },
        clearPermission: (state) => {
            state.permission = null;
        },
    },
});

export const { setPermission, clearPermission } = permissionSlice.actions;

export default permissionSlice.reducer;
