import { getMatchedByRequestedId } from "@/api/request/request";
import { useQuery } from "@tanstack/react-query";
import React from "react";

function useMatchedProperty({ property_id }: { property_id: string }) {
  const queryKey = ["matched_properties", property_id];
  const matchedPropertyQuery = useQuery({
    queryKey,
    queryFn: () =>
      getMatchedByRequestedId({
        id: property_id,
        page: "1",
      }),
  });
  return { matchedPropertyQuery, queryKey };
}

export default useMatchedProperty;
