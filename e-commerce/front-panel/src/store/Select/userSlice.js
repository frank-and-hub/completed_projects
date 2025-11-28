import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectUserData: null
};

const selectUserSlice = createSlice({
    name: 'selectUser',
    initialState,
    reducers: {
        setSelectUser: (state, action) => {
            state.selectUserData = action.payload.selectUser;
        },
        clearSelectUser: (state) => {
            state.selectUserData = null;
        },
    },
});

export const { setSelectUser, clearSelectUser } = selectUserSlice.actions;

export default selectUserSlice.reducer;
