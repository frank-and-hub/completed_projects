import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    selectBannerData: null
};

const selectBannerSlice = createSlice({
    name: 'selectBanner',
    initialState,
    reducers: {
        setSelectBanner: (state, action) => {
            state.selectBannerData = action.payload.selectBanner;
        },
        clearSelectBanner: (state) => {
            state.selectBannerData = null;
        },
    },
});

export const { setSelectBanner, clearSelectBanner } = selectBannerSlice.actions;

export default selectBannerSlice.reducer;
