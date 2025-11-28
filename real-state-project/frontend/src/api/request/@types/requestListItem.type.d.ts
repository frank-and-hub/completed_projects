interface countryType {
  country: string;
  id: number;
}

interface provinceType {
  province_name: string;
  id: string;
}

interface cityType {
  city: string;
  id: string;
}

interface suburbType {
  suburb_name: string;
  id: string;
}

interface currencyType {
  currency_name: string;
  currency_symbol: string;
}

interface advanced_featureType {
  Amenities_and_Lifestyle: Array<Amenities_and_LifestyleItemType>;
  Security_and_Access: Array<Security_and_AccessItemType>;
  Environment_and_Location: Array<Environment_and_LocationItemType>;
}

interface requestListItemType {
  id: string;
  created_at: string;
  country: countryType;
  province: provinceType;
  city: cityType;
  suburb: suburbType;
  currency: currencyType;
  property_type: string;
  start_price: string;
  end_price: string;
  no_of_bedroom: string;
  no_of_bathroom: string;
  pet_friendly: number;
  parking: number;
  pool: number;
  fully_furnished: number;
  garage: number;
  garden: number;
  move_in_date: any;
  property_count: number;
  advanced_feature: { [key: string]: { [key: string]: Array<string> } };
}

interface request_list_api extends paginationDataType<requestListItemType> {
  total_count: number;
}
