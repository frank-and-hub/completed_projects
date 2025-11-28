import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    roleTable: null
};

const roleTableSlice = createSlice({
    name: 'roleTable',
    initialState,
    reducers: {
        setRoleTable: (state, action) => {
            state.roleTable = action.payload.roleTable;
        },
        clearRoleTable: (state) => {
            state.roleTable = null;
        },
    },
});

export const { setRoleTable, clearRoleTable } = roleTableSlice.actions;

export default roleTableSlice.reducer;
