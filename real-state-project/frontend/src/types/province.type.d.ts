interface provinceItemType {
  id: string;
  name: string;
}
interface cityListItemType {
  id: number;
  name: string;
}
interface provinceListType {
  status: boolean;
  message: string;
  data: Array<provinceItemType>;
}

interface countryListItemType {
  currency_symbol: string;
  id: number;
  name: string;
}
