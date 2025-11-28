import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { get } from '@/utils/axios';
import { fetchOptions, Option, SelectOptionsState } from '@/types/CommonQueryParams';

const initialState: SelectOptionsState = {
    options: {},
    loading: {},
    hasMore: {},
    page: {},
};

export const fetchSelectOptions = createAsyncThunk(
    'select/fetchOptions',
    async ({
        key,
        url,
        page,
        labelKey,
        valueKey,
        search = '',
    }: fetchOptions) => {
        const limit = 1000,
            separator = url.includes('?') ? '&' : '?',
            finalUrl = `${url}${separator}page=${page}&limit=${limit}&search=${search}`,

            response = await get(finalUrl),
            data = response.data?.data || response.data || [],

            mapped: Option[] = data.map((item: any) => ({
                value: String(item[valueKey]),
                label: String(item[labelKey]),
            }));

        return {
            key,
            options: mapped,
            page,
            hasMore: mapped.length === limit,
        };
    }
);

const selectOptionsSlice = createSlice({
    name: 'selectOptions',
    initialState,
    reducers: {
        clearSelectOptions(state, action: PayloadAction<string>) {
            state.options[action.payload] = [];
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchSelectOptions.pending, (state, action) => {
                const key = action.meta.arg.key;
                state.loading[key] = true;
            })
            .addCase(fetchSelectOptions.fulfilled, (state, action) => {
                const { key, options, page, hasMore } = action.payload;

                const existing = state.options[key] || [];
                const merged = [...existing, ...options];
                const unique = Array.from(new Map(merged.map(item => [item.value, item])).values());

                state.options[key] = unique;
                state.page[key] = page + 1;
                state.hasMore[key] = hasMore;
                state.loading[key] = false;
            })
            .addCase(fetchSelectOptions.rejected, (state, action) => {
                const key = action.meta.arg.key;
                state.loading[key] = false;
            });
    },
});

export const { clearSelectOptions } = selectOptionsSlice.actions;
export default selectOptionsSlice.reducer;
