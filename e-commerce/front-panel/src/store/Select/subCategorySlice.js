import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectSubCategoryData: null
};

const selectSubCategorySlice = createSlice({
    name: 'selectSubCategory',
    initialState,
    reducers: {
        setSelectSubCategory: (state, action) => {
            state.selectSubCategoryData = action.payload.selectSubCategory;
        },
        clearSelectSubCategory: (state) => {
            state.selectSubCategoryData = null;
        },
    },
});

export const { setSelectSubCategory, clearSelectSubCategory } = selectSubCategorySlice.actions;

export default selectSubCategorySlice.reducer;
