import LandlordsCard from "../../list-your-property-for-rent/landlords/home2/components/landlordsCard/LandlordsCard";
import { Center, Container, Flex, SimpleGrid } from "@mantine/core";
import Image from "next/image";
import React from "react";

export const data = [
  {
    id: 1,
    title: "Flats to rent in Durban",
    value: "Affordable city living near the beach.",
  },
  {
    id: 2,
    title: "Houses to rent in Ballito",
    value: "Secure estates and luxury coastal homes.",
  },
  {
    id: 3,
    title: "Flats to rent in Richards Bay",
    value: "Ideal for professionals working in the area.",
  },
  {
    id: 4,
    title: "Accommodation to rent in Pietermaritzburg",
    value: "Budget-friendly rentals for students and young professionals.",
  },
  {
    id: 5,
    title: "Apartments to rent in Hillcrest",
    value: "Perfect for those seeking suburban tranquility",
  },
  // {
  //   id: 6,
  //   title: "Affordable Housing & Upcoming Projects",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
];
function City2PropertiesAvailable() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2>Types of Rental Properties Available in KwaZulu-Natal</h2>
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

export default City2PropertiesAvailable;
