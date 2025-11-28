import { transactionHistory } from '@/api/subscription/subscription';
import { transactionQueryKey } from '@/utils/queryKeys/transactionHistoryKeys';
import { useQuery } from '@tanstack/react-query';

const useTransactionHistory = () => {
  const { data, isLoading } = useQuery<transactionListType, Error>({
    queryKey: [...transactionQueryKey.list],
    queryFn: () => transactionHistory(),
  });
  return { isLoading, data };
};

export default useTransactionHistory;
