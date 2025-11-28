interface matchedPropertyItemType {
  id: string;
  title: string;
  address: string;
  image: string;
  property_id: string;
  details_url: string;
  contract: string;
  contract_status:
    | "tenant_pending"
    | "approval_pending"
    | "agency_pending"
    | "rejected"
    | "completed";
  contract_id?: string;
  admin_id?: string;
}

interface matchedPropertyListType {
  property: Array<matchedPropertyItemType>;
  total_count: number;
}

interface matchedPropertyType {
  status: boolean;
  message: string;
  data: matchedPropertyListType;
  meta: {
    current_page: number;
    total_item: number;
    total_page: number;
  };
}
