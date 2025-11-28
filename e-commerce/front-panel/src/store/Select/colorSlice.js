import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectColorData: null
};

const selectColorSlice = createSlice({
    name: 'selectColor',
    initialState,
    reducers: {
        setSelectColor: (state, action) => {
            state.selectColorData = action.payload.selectColor;
        },
        clearSelectColor: (state) => {
            state.selectColorData = null;
        },
    },
});

export const { setSelectColor, clearSelectColor } = selectColorSlice.actions;

export default selectColorSlice.reducer;
