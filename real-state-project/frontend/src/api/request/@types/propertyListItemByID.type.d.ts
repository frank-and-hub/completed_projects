interface propertyListItemByID {
  credit_reports_status: "unapproved" | "approved";
  contract: string;
  contract_status:
    | "tenant_pending"
    | "approval_pending"
    | "agency_pending"
    | "rejected"
    | "completed";
  contract_id?: string;
  address: string;
  admin_id: string;
  contract_id: null | string;
  contract_status: 0 | 1 | 2 | 3;
  details_url: string;
  id: string;
  image: string;
  property_id: string;
  title: string;
  property: {
    event: {
      id: string;
      date: string;
      time: string;
      date_time: string;
      status: string;
      time_limit: boolean;
    };
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
    photos: [
      {
        id: string;
        image: string;
        isMain: 1;
      },
      {
        id: string;
        image: string;
        isMain: 0;
      }
    ];
    description: string;
    advanced_feature: {
      Amenities_and_Lifestyle: [];
      Security_and_Access: [];
      Environment_and_Location: [];
    };
    property_handle_details: {
      fullName: string;
      email: string;
      phone: string;
      image: string;
      role: string;
      id: string;
    };
    client: {
      name: null;
      logo: null;
    };
  };
}
