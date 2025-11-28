import { getPropertyCount } from "@/api/propertySearchHistory/propertySearch";
import { queryClient } from "@/utils/queryClient";
import { dehydrate } from "@tanstack/react-query";

const cityListCountPrefetch = async () => {
  queryClient.prefetchQuery({
    queryKey: ["property_count"],
    queryFn: getPropertyCount,
  });

  return dehydrate(queryClient);

  // try {
  //   return queryClient.prefetchQuery({
  //     queryKey: ["property_count"],
  //     queryFn: getPropertyCount,
  //   });

  //   // const propertiesCount = queryClient.getQueryData<IObject>(["property_count"]);

  //   // const cityListSchema = {
  //   //   "@context": "https://schema.org",
  //   //   "@type": "ItemList",
  //   //   name: "Top Cities for Rent",
  //   //   itemListElement: Object.keys(propertiesCount ?? {})?.map(
  //   //     (cityName, index) => ({
  //   //       "@type": "RealEstateListing",
  //   //       position: index + 1,
  //   //       name: cityName,

  //   //       count: propertiesCount ? propertiesCount[cityName] : "",
  //   //     })
  //   //   ),
  //   // };

  //   // return cityListSchema;
  // } catch (error) {
  //   throw new Error("Error prefetching city list count");
  // }
};

export default cityListCountPrefetch;
