import { Center, Container, Flex, SimpleGrid } from "@mantine/core";
import React from "react";
//import { data } from "../gauteng/CityPropertiesAvailable";
import LandlordsCard from "../../list-your-property-for-rent/landlords/home2/components/landlordsCard/LandlordsCard";
import Image from "next/image";

export const data = [
  {
    id: 1,
    title: "Apartments to rent in Cape Town",
    value: "Ideal for city living.",
  },
  {
    id: 2,
    title: "Houses to rent in Paarl",
    value: "Spacious homes in a scenic setting.",
  },
  {
    id: 3,
    title: "Flats to rent in Stellenbosch",
    value: "Perfect for students and professionals.",
  },
  {
    id: 4,
    title: "Flats to rent in Somerset West & George",
    value: "Affordable coastal options.",
  },
  {
    id: 5,
    title: "2-bedroom apartments to rent in Cape Town",
    value: "Great for families or shared living.`",
  },
  // {
  //   id: 6,
  //   title: "Affordable Housing & Upcoming Projects",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
];

function City5PropertiesAvailable() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2>Types of Rental Properties Available in western cape</h2>
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

export default City5PropertiesAvailable;
