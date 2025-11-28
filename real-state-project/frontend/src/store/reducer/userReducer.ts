import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface propertySearchDataType {
  country_name?: string;
  province_name?: string;
  city?: string;
  suburb_name?: string;
  property_type?: string;
  start_price?: string;
  end_price?: string;
  no_of_bedroom?: string;
  no_of_bathroom?: string;
  move_in_date?: Date | null;
}
interface UProps {
  token?: string | undefined;
  userDetail: userInformationType | undefined;
  propertySearchData: propertySearchDataType | undefined;
  isPropertySearch: boolean;
  isShowMap: 0 | 1;
}

const initialState: UProps = {
  token: undefined,
  userDetail: undefined,
  propertySearchData: undefined,
  isPropertySearch: false,
  isShowMap: 0,
};

const userSlice = createSlice({
  initialState,
  name: "user",
  reducers: {
    updateToken(state: UProps, action: PayloadAction<userDetailType>) {
      state.token = action.payload.token;
      state.userDetail = action.payload?.user;
    },
    updateUserInformation(
      state: UProps,
      action: PayloadAction<userInformationType | undefined>
    ) {
      state.userDetail = action.payload;
    },
    updatePropertyInformation(
      state: UProps,
      action: PayloadAction<propertySearchDataType | undefined>
    ) {
      state.propertySearchData = action.payload;
    },
    updatePropertySearch(state: UProps, action: PayloadAction<boolean>) {
      state.isPropertySearch = action.payload;
    },
    updateIsShowMap(state: UProps, action: PayloadAction<0 | 1>) {
      state.isShowMap = action.payload;
    },
  },
});

export const {
  updateToken,
  updateUserInformation,
  updatePropertyInformation,
  updatePropertySearch,
  updateIsShowMap,
} = userSlice.actions;

export default userSlice.reducer;
