type otpValueType = "general" | "forgot" | "register" | "";
type otpVerificationType = "mobile" | "email" | "";
type additionalType =
  | "fully_furnished"
  | "garage"
  | "garden"
  | "parking"
  | "pet_friendly"
  | "pool";
interface contextValuesType {
  isLandLord: boolean;
  otpType: otpValueType;
  userId: string;
  otpVerificationType: otpVerificationType;
  email: string;
  profileThings: {
    name: string;
    image: any;
    phone: string;
    verifytype: "both" | "onlyphone" | "onlypass" | "" | string;
    verifyToken: string;
    countryCode: string;
    type: "password" | "phone" | "";
    new_password?: string;
    country?: string;
  };
  other: "mobile" | "";
  propertySearchData: {
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
    currency?: string;
  };
  province_Id?: string;
  country_Id?: number;
  cityId?: string;
  suburbId?: string;
  currency?: string;
  advanceFeatureSelectedData?: Array<{
    title: string;
    value: Array<string>;
    id: number;
  }>;
  advanceFeatureData?: {
    [key: string]: Array<{ title: string; value: string }>;
  } | null;
  requestAgainData?: { [key: string]: { [key: string]: Array<string> } };
  isSearchApiCall: boolean;
  subscriptionId: string;
  amount: string;
  property_id: string;
  locationData?: {
    city: menuType | null;
    country: menuType | null;
    province: menuType | null;
    suburb: menuType | null;
  };
}

type menuType = {
  id: string;
  label: string;
  value: string;
};
interface contextType {
  phone: string;
  setPhone: Dispatch<SetStateAction<string>>;
  isModalOpen: modalType;
  setIsModalOpen: Dispatch<SetStateAction<modalType>>;
  setContextValue: Dispatch<SetStateAction<contextValuesType>>;
  contextValue: contextValuesType;
}
