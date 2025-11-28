interface financialsType {
  price: string;
  currency_symbol: string;
  currency: string;
}

interface main_imageType {
  id: string;
  path: string;
}

interface lat_lngItemType {
  0: string;
  id: string;
  lat: string;
  lng: string;
  title: string;
  address: string;
  financials: financialsType;
  main_image: main_imageType;
}

interface markerDataDetailsType {
  is_show_map: number;
  lat_lng: Array<lat_lngItemType>;
}

interface markerDataType {
  data: markerDataDetailsType;
}
