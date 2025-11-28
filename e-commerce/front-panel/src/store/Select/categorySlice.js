import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectCategoryData: null
};

const selectCategorySlice = createSlice({
    name: 'selectCategory',
    initialState,
    reducers: {
        setSelectCategory: (state, action) => {
            state.selectCategoryData = action.payload.selectCategory;
        },
        clearSelectCategory: (state) => {
            state.selectCategoryData = null;
        },
    },
});

export const { setSelectCategory, clearSelectCategory } = selectCategorySlice.actions;

export default selectCategorySlice.reducer;
