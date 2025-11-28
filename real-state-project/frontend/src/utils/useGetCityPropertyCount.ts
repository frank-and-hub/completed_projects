import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import { useQuery } from "@tanstack/react-query";

function useGetCityPropertyCount() {
  return useQuery({
    queryKey: ["property_count"],
    queryFn: getPropertyCount,
  });
}

export default useGetCityPropertyCount;
