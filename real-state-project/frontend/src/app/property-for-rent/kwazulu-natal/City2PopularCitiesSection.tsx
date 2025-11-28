import { Box, Center, Container, Flex, Group, Text } from "@mantine/core";
import Image from "next/image";
import React from "react";
const RentalPropertiesSectionData = [
  { id: 1, name: "Durban" },
  { id: 2, name: "KwaZulu-Natal" },
  { id: 3, name: "Western Cape" },
  { id: 4, name: "Mpumalanga" },
  { id: 5, name: "Eastern Cape" },
];
function City2PopularCitiesSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="rental_properties_container">
          <Center p={40}>
            <div style={{ width: "80%" }}>
              <h2 style={{ textAlign: "center", textTransform: "uppercase" }}>
                Popular Cities In KwaZulu-Natal
              </h2>
              <Group mt={25} justify="center">
                {RentalPropertiesSectionData.map((item, index) => (
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

export default City2PopularCitiesSection;
