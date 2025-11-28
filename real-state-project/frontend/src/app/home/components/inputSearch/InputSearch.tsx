"use client";
import { Input } from "@mantine/core";
import { IconBuildingSkyscraper } from "@tabler/icons-react";
import React from "react";
import useSectionOne from "../../useSectionOne";

function InputSearch() {
  const { placeInputRef } = useSectionOne();

  return (
    <>
      <Input
        flex={1}
        visibleFrom="md"
        placeholder="Where do you want to stay? (e.g. Sea Point, Sandton)"
        ref={placeInputRef}
        leftSection={<IconBuildingSkyscraper color="black" stroke={1.5} />}
      />
      <Input
        flex={1}
        hiddenFrom="md"
        placeholder="Where do you want to stay? (e.g. Cape Town)"
        ref={placeInputRef}
        leftSection={<IconBuildingSkyscraper color="black" stroke={1.5} />}
      />
    </>
  );
}

export default InputSearch;
