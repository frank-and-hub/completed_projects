interface move_in_dateType {}

interface property_needsItemType {
  id: string;
  created_at: string;

  city: { city: string; id: string };
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
  move_in_date: Date;
  currency: {
    currency_name: string;
    currency_symbol: string;
  };
  province: { id: string; province_name: string };
  suburb: { id: string; suburb_name: string };
  country: { id: number; country: string };
  advanced_feature: { [key: string]: { [key: string]: Array<string> } };
}

interface propertyNeedsListType {
  property_needs: Array<property_needsItemType>;
  total_count: number;
  date_range_count: string;
}

interface propertyNeedsType {
  status: boolean;
  message: string;
  data: propertyNeedsListType;
}
