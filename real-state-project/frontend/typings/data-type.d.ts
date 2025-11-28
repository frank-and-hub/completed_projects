
interface propertyType {
   id: string;
   name: string;
   address: string;
}

interface clientType {
   name: string;
   logo: string;
}

interface rootElementType {
   id: string;
   pvr_date: string;
   property: propertyType;
   property_user_name: string;
   client: clientType;
}
