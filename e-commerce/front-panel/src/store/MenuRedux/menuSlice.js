import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import { get } from 'utils/AxiosUtils'

// Thunks to fetch both menus
export const fetchMenus = createAsyncThunk('menu/fetchMenus', async (_, { rejectWithValue }) => {
  try {
    const { response } = get('/menus');
    if (!response) throw new Error('Failed to fetch menus');
    return response.data; // Return response for the Redux store
  } catch (error) {
    return rejectWithValue(error.message); // Pass error to Redux state
  }
});

export const fetchFilteredMenus = createAsyncThunk('menu/fetchFilteredMenus', async (_, { rejectWithValue }) => {
  try {
    const { response } = get('/menus?filter_type=true');
    if (!response) throw new Error('Failed to fetch filtered menus');
    return response.data; // Return response for the Redux store
  } catch (error) {
    return rejectWithValue(error.message);
  }
});

// Redux slice for handling menu data
const menuSlice = createSlice({
  name: 'menu',
  initialState: {
    menus: [],
    filteredMenus: [],
    loading: false,
    error: null,
  },
  reducers: {
    clearMenus: (state) => {
      state.menus = [];
      state.filteredMenus = [];
    },

    setMenuFilterData: (state, action) => {
      state.menus = state.payload.menus;
      state.filteredMenus = state.payload.menusFilter;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchMenus.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchMenus.fulfilled, (state, action) => {
        state.menus = action.payload;
        state.loading = false;
      })
      .addCase(fetchMenus.rejected, (state, action) => {
        state.error = action.payload;
        state.loading = false;
      })
      .addCase(fetchFilteredMenus.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchFilteredMenus.fulfilled, (state, action) => {
        state.filteredMenus = action.payload;
        state.loading = false;
      })
      .addCase(fetchFilteredMenus.rejected, (state, action) => {
        state.error = action.payload;
        state.loading = false;
      });
  },
});

export const { clearMenus, setMenuFilterData } = menuSlice.actions;
export default menuSlice.reducer;
