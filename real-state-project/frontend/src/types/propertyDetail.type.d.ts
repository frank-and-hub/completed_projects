interface openparkingsType {}

interface photosItemType {
  image: string;
  isMain: number;
}

interface contactsItemType {
  fullName: string;
  image: string;
  phone: string;
  email: string;
}

interface otherFeatureType {
  carports: number;
  livingAreas: number;
  propertyFeatures: string;
  staffAccommodation: number;
  study: number;
}
interface propertyDetailItemType {
  id: string;
  client_office_id: string;
  price: string;
  currency: string;
  currency_symbol: string;
  propertyType: string;
  propertyStatus: string;
  address: string;
  beds: string;
  baths: string;
  pool: boolean;
  study: number;
  livingAreas: number;
  staffAccommodation: number;
  carports: number;
  garages: number;
  petsAllowed: boolean;
  propertyFeatures: string;
  title: string;
  openparkings: openparkingsType;
  furnished: boolean;
  buildingSize: string;
  buildingSizeType: string;
  landSize: string;
  landsizeType: string;
  description: string;
  photos: Array<photosItemType> | string[];
  contacts: Array<contactsItemType>;
  other_features: otherFeatureType;
  client: { logo: string; name: string };
  town?: string;
}

interface propertyDetailType {
  status: boolean;
  message: string;
  data: propertyDetailItemType;
}
