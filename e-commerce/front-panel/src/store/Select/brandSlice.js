import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectBrandData: null
};

const selectBrandSlice = createSlice({
    name: 'selectBrand',
    initialState,
    reducers: {
        setSelectBrand: (state, action) => {
            state.selectBrandData = action.payload.selectBrand;
        },
        clearSelectBrand: (state) => {
            state.selectBrandData = null;
        },
    },
});

export const { setSelectBrand, clearSelectBrand } = selectBrandSlice.actions;

export default selectBrandSlice.reducer;
