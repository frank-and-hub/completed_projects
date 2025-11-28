import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    activeSubMenu: null
};

const activeSubMenuSlice = createSlice({
    name: 'activeSubMenu',
    initialState,
    reducers: {
        setActiveSubMenu: (state, action) => {
            state.activeSubMenu = action.payload.activeSubMenu;
        },
        clearActiveSubMenu: (state) => {
            state.activeSubMenu = null;
        },
    },
});

export const { setActiveSubMenu, clearActiveSubMenu } = activeSubMenuSlice.actions;

export default activeSubMenuSlice.reducer;
