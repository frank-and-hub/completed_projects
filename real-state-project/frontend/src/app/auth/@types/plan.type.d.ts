interface planItemType {
  plan_name: string;
  amount: string;
  id: string;
  type: 'tenant' | '"agency"' | 'privatelandlord';
}

interface planAmountType {
  status: boolean;
  message: string;
  data: Array<planItemType>;
}

interface priceRangeItemType {
  start_price: string;
  end_price: string;
  currency: string;
}

interface priceRangeType {
  status: boolean;
  message: string;
  data: Array<priceRangeItemType>;
}
