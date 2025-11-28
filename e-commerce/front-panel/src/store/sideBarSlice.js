import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    sideBar: null
};

const sideBarSlice = createSlice({
    name: 'sideBar',
    initialState,
    reducers: {
        setSideBar: (state, action) => {
            state.sideBar = action.payload.sideBar;
        },
        clearSideBar: (state) => {
            state.sideBar = null;
        },
    },
});

export const { setSideBar, clearSideBar } = sideBarSlice.actions;

export default sideBarSlice.reducer;
