interface linkType {}

interface adminType {
  name: string;
  image: string;
}

interface addressType {
  streetName: string;
  unitNumber: string;
  complexName: string;
  streetNumber: string;
}

interface propertyType {
  address: addressType;
  country: string;
  province: string;
  town: string;
  suburb: string;
  complete_address: string;
}

interface calendarEventListItemType {
  id: string;
  date: string;
  time: string;
  title: string;
  description: string;
  link: linkType;
  status: string;
  admin: adminType;
  property: propertyType;
  complete_address: string;
}
