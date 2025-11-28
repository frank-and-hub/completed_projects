import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    iconData: null
};

const iconSlice = createSlice({
    name: 'icon',
    initialState,
    reducers: {
        setIcon: (state, action) => {
            state.iconData = action.payload.icon;
        },
        clearIcon: (state) => {
            state.iconData = null;
        },
    },
});

export const { setIcon, clearIcon } = iconSlice.actions;

export default iconSlice.reducer;
