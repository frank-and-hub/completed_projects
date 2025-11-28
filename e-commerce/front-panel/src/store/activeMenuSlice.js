import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    activeMenu: null
};

const activeMenuSlice = createSlice({
    name: 'activeMenu',
    initialState,
    reducers: {
        setActiveMenu: (state, action) => {
            state.activeMenu = action.payload;
        },
        clearActiveMenu: (state) => {
            state.activeMenu = null;
        },
    },
});

export const { setActiveMenu, clearActiveMenu } = activeMenuSlice.actions;

export default activeMenuSlice.reducer;
