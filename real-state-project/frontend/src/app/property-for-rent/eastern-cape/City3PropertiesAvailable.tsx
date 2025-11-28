import { Center, Container, Flex, SimpleGrid } from "@mantine/core";
import React from "react";
//import { data } from "../gauteng/CityPropertiesAvailable";
import LandlordsCard from "../../list-your-property-for-rent/landlords/home2/components/landlordsCard/LandlordsCard";
import Image from "next/image";

export const data = [
  {
    id: 1,
    title: "Houses to rent in East London",
    value: "Spacious homes for families.",
  },
  {
    id: 2,
    title: "Apartments to rent in East London, South Africa ",
    value: "City living with modern amenities.",
  },
  {
    id: 3,
    title: "Flats to rent in Jeffreys Bay & Mthatha",
    value: "Affordable and coastal options.",
  },
  {
    id: 4,
    title: "Property for rent in Gqeberha",
    value: "Diverse choices from budget to luxury rentals.",
  },
  {
    id: 5,
    title: " Flats to rent in Grahamstown (Makhanda)",
    value: "Ideal for students and professionals.",
  },
  // {
  //   id: 6,
  //   title: "Affordable Housing & Upcoming Projects",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
];

function City3PropertiesAvailable() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2>Types of Rental Properties Available in Eastern Cape</h2>
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

export default City3PropertiesAvailable;
