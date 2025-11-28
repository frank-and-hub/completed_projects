import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectRoleData: null
};

const selectRoleSlice = createSlice({
    name: 'selectRole',
    initialState,
    reducers: {
        setSelectRole: (state, action) => {
            state.selectRoleData = action.payload.selectRole;
        },
        clearSelectRole: (state) => {
            state.selectRoleData = null;
        },
    },
});

export const { setSelectRole, clearSelectRole } = selectRoleSlice.actions;

export default selectRoleSlice.reducer;
