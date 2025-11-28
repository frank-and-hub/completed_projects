"use client";
import { getDummyProperties } from "@/api/dummyProperties/dummyProperties";
import FeaturedPropertiesCard from "@/app/home/components/featuredPropertiesCard/FeaturedPropertiesCard";
import { properties } from "@/data/properties";
import { Box, Container, Flex, SimpleGrid } from "@mantine/core";
import { useQuery } from "@tanstack/react-query";
import React from "react";

function City3FeaturedProperties() {
  const queryData = useQuery({
    queryKey: ["dummyProperties"],
    queryFn: getDummyProperties,
  });
  return (
    <section className="homeCard_sec" style={{ backgroundColor: "#FBFBFB" }}>
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>Featured Properties</p>
          </Box>
          <h2>Explore Top Rental Properties</h2>
          <h3>Check out our simple Step-by-Step Process</h3>
        </Flex>
        <SimpleGrid cols={3} className="simple-grid-feature-properties">
          {queryData?.data?.map((property, index) => (
            <FeaturedPropertiesCard property={property} key={index} />
          ))}
        </SimpleGrid>
      </Container>
    </section>
  );
}

export default City3FeaturedProperties;
