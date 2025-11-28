interface transactionItemType {
  purchase_date: string;
  transcation_id: string;
  active_period: string;
  plan_type: string;
  amount: string;
  started_at: string;
  expired_at: string;
  no_of_requests: number;
}

interface transactionListType {
  status: boolean;
  message: string;
  data: Array<transactionItemType>;
}
