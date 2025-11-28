import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectDiscountData: null
};

const selectDiscountSlice = createSlice({
    name: 'selectDiscount',
    initialState,
    reducers: {
        setSelectDiscount: (state, action) => {
            state.selectDiscountData = action.payload.selectDiscount;
        },
        clearSelectDiscount: (state) => {
            state.selectDiscountData = null;
        },
    },
});

export const { setSelectDiscount, clearSelectDiscount } = selectDiscountSlice.actions;

export default selectDiscountSlice.reducer;
