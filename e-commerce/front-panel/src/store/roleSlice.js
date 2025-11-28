import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    role: null
};

const roleSlice = createSlice({
    name: 'role',
    initialState,
    reducers: {
        setRole: (state, action) => {
            state.role = action.payload.role;
        },
        clearRole: (state) => {
            state.role = null;
        },
    },
}); 

export const { setRole, clearRole } = roleSlice.actions;

export default roleSlice.reducer;
