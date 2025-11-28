interface Amenities_and_LifestyleType {
  furnishing: Array<string>;
  kitchen_features: Array<string>;
  cooling_heating: Array<string>;
  laundry_facilities: Array<string>;
  technology: Array<string>;
}

interface Security_and_AccessType {
  security_features: Array<string>;
  parking: Array<string>;
  lease_options: Array<string>;
  pet_policy: Array<string>;
  fire_safety_features: Array<string>;
}

interface Environment_and_LocationType {
  location_views: Array<string>;
  outdoor_areas: Array<string>;
  energy_efficiency: Array<string>;
}

interface photosItemType {
  id: string;
  path: string;
}

interface connectivityType {}

interface mediaType {}

interface contactsType {
  name: string;
  email: string;
  phone: string;
  media: mediaType;
}

interface advanceFeatureType {
  Amenities_and_Lifestyle: Amenities_and_LifestyleType;
  Security_and_Access: Security_and_AccessType;
  Environment_and_Location: Environment_and_LocationType;
}
interface mapPropertyDetailsItemType {
  id: string;
  price: string;
  currency: string;
  currency_symbol: string;
  title: string;
  address: string;
  landSize: string;
  landSize_unit: string;
  buildingSize: string;
  buildingSize_unit: string;
  propertyType: string;
  propertyStatus: string;
  beds: string;
  baths: string;
  photos: Array<photosItemType>;
  connectivity: connectivityType;
  parking: Array<string>;
  contacts: contactsType;
  advanced_feature: advanceFeatureType;
  description: string;
  property_handle_details: {
    fullName: string;
    image: string;
    phone: string;
    email: string;
    role: "agent" | "agency" | "privatelandlord";
  };
  client: {
    name?: string;
    logo?: string;
  };
}

interface mapPropertyDetailsType {
  status: boolean;
  message: string;
  data: mapPropertyDetailsItemType;
}

type advanceFeatureDataShowType = Array<{
  title: string;
  content: Array<{ title: string; content: Array<string> }>;
}>;
