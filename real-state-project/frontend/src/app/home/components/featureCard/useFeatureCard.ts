import { featureList } from '@/api/feature/feature';
import { FeatureListListQueryKey } from '@/utils/queryKeys/featureListQueryKeys';
import { useQuery } from '@tanstack/react-query';

const useFeatureCard = () => {
  const { data, isPending, isError } = useQuery<featureList, Error>({
    queryKey: [...FeatureListListQueryKey.list],
    queryFn: () => featureList(),
  });

  return { data, isPending, isError };
};

export default useFeatureCard;
