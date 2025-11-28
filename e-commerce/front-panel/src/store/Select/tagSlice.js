import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectTagData: null
};

const selectTagSlice = createSlice({
    name: 'selectTag',
    initialState,
    reducers: {
        setSelectTag: (state, action) => {
            state.selectTagData = action.payload.selectTag;
        },
        clearSelectTag: (state) => {
            state.selectTagData = null;
        },
    },
});

export const { setSelectTag, clearSelectTag } = selectTagSlice.actions;

export default selectTagSlice.reducer;
