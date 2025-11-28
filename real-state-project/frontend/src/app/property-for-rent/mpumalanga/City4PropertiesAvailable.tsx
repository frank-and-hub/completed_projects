import { Center, Container, Flex, SimpleGrid } from "@mantine/core";
import React from "react";
//import { data } from "../gauteng/CityPropertiesAvailable";
import LandlordsCard from "../../list-your-property-for-rent/landlords/home2/components/landlordsCard/LandlordsCard";
import Image from "next/image";

export const data = [
  {
    id: 1,
    title: "Houses to rent in Nelspruit & Witbank",
    value: "Perfect for families.",
  },
  {
    id: 2,
    title: "Flats to rent in Secunda & Middelburg",
    value: "Budget-friendly options.",
  },
  {
    id: 3,
    title: "Flat to rent in White River",
    value: "Tranquil surroundings with nature access.",
  },
  {
    id: 4,
    title: "Secunda accommodation to rent",
    value: "Ideal for professionals working in the energy sector.",
  },
  {
    id: 5,
    title: "House to rent in Middelburg",
    value: "Spacious homes in a growing community.",
  },
  // {
  //   id: 6,
  //   title: "Affordable Housing & Upcoming Projects",
  //   value: "Apartments, Townhouses,Luxury Villas",
  // },
];

function City4PropertiesAvailable() {
  return (
    // <section className="homeCard_sec">
    //   <Container size={"lg"}>
    //     <div className="city_properties_available_container">
    //       <div className="left-side-container">
    //         <h2 style={{ textTransform: "uppercase" }}>
    //           Best Features for Landlords
    //         </h2>
    //         <SimpleGrid mt={15} cols={2}>
    //           {data?.map((item, index) => (
    //             <LandlordsCard item={item} />
    //           ))}
    //         </SimpleGrid>
    //       </div>
    //       <div className="right-side-container">
    //         <Center mt={15}>
    //           <Image
    //             src={require("../../../../assets/images/properties_available_img.png")}
    //             alt="no_img"
    //             style={{ height: "100%" }}
    //           />
    //         </Center>
    //       </div>
    //     </div>
    //   </Container>
    // </section>
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2>Types of Rental Properties Available in Mpumalanga</h2>
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

export default City4PropertiesAvailable;
