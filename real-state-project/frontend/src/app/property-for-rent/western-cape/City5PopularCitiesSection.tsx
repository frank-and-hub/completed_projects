import { Box, Center, Container, Flex, Group, Text } from "@mantine/core";
import React from "react";
//import { RentalPropertiesSectionData } from "../gauteng/CityPopularCitiesSection";
import Image from "next/image";

export const RentalPropertiesSectionData2 = [
  { id: 1, name: "Sandtonnnnn", count: 30 },
  { id: 2, name: "Johannesburg", count: 20 },

  {
    id: 3,
    name: "Pretoria",
    count: 40,
  },
  {
    id: 4,
    name: "Midrand",
    count: 35,
  },
  { id: 5, name: "Krugersdorp", count: 28 },
];
function City5PopularCitiesSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="rental_properties_container">
          <Center p={40}>
            <div style={{ width: "80%" }}>
              <h2 style={{ textAlign: "center", textTransform: "uppercase" }}>
                Popular Cities In Western Capeeeeeeeee
              </h2>
              <Group mt={25} justify="center">
                {RentalPropertiesSectionData2.map((item, index) => (
                  <Flex className="rental_properties_card" key={index}>
                    <Text fz={16} fw={500}>
                      {item?.name}
                    </Text>
                    <Box className="rental_properties_card_icon">
                      <Image
                        src={require("../../../../assets/svg/home_with_tree.svg")}
                        alt={"no_image"}
                        height={35}
                        width={35}
                      />
                    </Box>
                  </Flex>
                ))}
              </Group>
            </div>
          </Center>
        </div>
      </Container>
    </section>
  );
}

export default City5PopularCitiesSection;
