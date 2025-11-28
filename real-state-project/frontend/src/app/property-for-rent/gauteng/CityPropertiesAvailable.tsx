import LandlordsCard from "../../list-your-property-for-rent/landlords/home2/components/landlordsCard/LandlordsCard";

import { Center, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import Image from "next/image";
import React from "react";
export const data = [
  {
    id: 1,
    title: "1-bedroom apartments",
    value: "Ideal for singles and professionals.",
  },
  {
    id: 2,
    title: "Luxury apartments",
    value: "Available in Sandton, Midrand, and Waterfall City.",
  },
  {
    id: 3,
    title: "Flats to rent in Johannesburg",
    value: "Budget-friendly for city dwellers.",
  },
  {
    id: 4,
    title: "Family homes",
    value: "Townhouses and duplexes in safe, gated communities.",
  },
  // {
  //   id: 5,
  //   title: "Vacation Homes & Airbnb Rentals",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
  // {
  //   id: 6,
  //   title: "Affordable Housing & Upcoming Projects",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
];
function CityPropertiesAvailable() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2>Types of Rental Properties Available in Gauteng</h2>
            <SimpleGrid mt={20} cols={{ base: 1, sm: 2, lg: 2 }}>
              {data?.map((item, index) => (
                <LandlordsCard item={item} />
              ))}
            </SimpleGrid>
          </div>
          <Flex mt={20} justify={"center"}>
            <Image
              src={require("../../../../assets/images/properties_available_img.png")}
              alt="no_img"
              style={{ height: "100%" }}
            />
          </Flex>
        </div>
      </Container>
    </section>
  );
}

export default CityPropertiesAvailable;
