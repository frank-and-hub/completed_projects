"use client";
import {
  createContext,
  SetStateAction,
  Dispatch,
  useState,
  useContext,
} from "react";

const contextValueDefault: contextValuesType = {
  isLandLord: false,
  otpType: "",
  userId: "",
  otpVerificationType: "",
  email: "",
  other: "",
  profileThings: {
    image: "",
    name: "",
    phone: "",
    verifytype: "",
    verifyToken: "",
    countryCode: "",
    type: "",
  },

  isSearchApiCall: false,
  subscriptionId: "",
  amount: "",
  property_id: "",
  propertySearchData: {
    move_in_date: null,
    city: "",
    country_name: "",
    end_price: "",
    no_of_bathroom: "",
    no_of_bedroom: "",
    property_type: "",
    province_name: "",
    start_price: "",
    suburb_name: "",
  },
};
const contextDefaultValue: contextType = {
  phone: "",
  setPhone: (): string => "",
  isModalOpen: "",
  setIsModalOpen: (): modalType => "",
  setContextValue: (() => {}) as Dispatch<SetStateAction<contextValuesType>>, // ✅ Correct type
  contextValue: contextValueDefault,
};

const Context = createContext<contextType>(contextDefaultValue);

export const GlobalContextProvider = ({
  children,
}: {
  children: React.ReactNode;
}) => {
  const [phone, setPhone] = useState<string>("");
  const [isModalOpen, setIsModalOpen] = useState<modalType>("");
  const [contextValue, setContextValue] =
    useState<contextValuesType>(contextValueDefault);

  return (
    <Context.Provider
      value={{
        phone,
        setPhone,
        isModalOpen,
        setIsModalOpen,
        contextValue,
        setContextValue,
      }}
    >
      {children}
    </Context.Provider>
  );
};

export const useGlobalContext = () => useContext(Context);
