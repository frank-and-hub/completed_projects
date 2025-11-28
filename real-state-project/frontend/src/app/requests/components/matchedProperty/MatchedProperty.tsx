import React from "react";
import useMatchedProperty from "./useMatchedProperty";
import { Center, Loader } from "@mantine/core";
import MatchedPropertyCard from "./MatchedPropertyCard";

function MatchedProperty({ id }: { id: string }) {
  const {
    matchedPropertyQuery: { data, isLoading, isError, error },
    queryKey,
  } = useMatchedProperty({ property_id: id });

  return isLoading ? (
    <Center>
      <Loader size={32} />
    </Center>
  ) : (
    data?.map((property, index) => (
      <MatchedPropertyCard
        key={index}
        index={index}
        property={property}
        queryKey={queryKey}
        request_id={id}
      />
    ))
  );
}

export default MatchedProperty;
