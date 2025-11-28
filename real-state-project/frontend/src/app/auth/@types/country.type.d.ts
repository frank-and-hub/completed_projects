interface countryListItemType {
  id: number;
  name: string;
  currency_symbol: string;
}
interface stateListItemType {
  id: number;
  name: string;
}

interface cityListItemType extends stateListItemType {}

// type countryListType = Array<countryListItemType>;
type stateListType = Array<stateListItemType>;
interface countryListType {
  data: Array<countryListItemType>;
  status: boolean;
  meta: {
    total_page: number;
    current_page: number;
    total_item: number;
    per_page: number;
  };
  link: {
    next: boolean;
    prev: boolean;
  };
}
type cityListType = Array<cityListItemType>;
