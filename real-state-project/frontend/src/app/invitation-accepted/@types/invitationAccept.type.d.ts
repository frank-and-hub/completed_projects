interface clientType {
  name: string;
  logo: string;
}

interface invitationAcceptType {
  id: string;
  pvr_date: string;
  property: {
    id: string;
    name: string;
    address: string;
    type: "internal" | "external";
  };
  property_user_name?: string;
  client: clientType;
}
