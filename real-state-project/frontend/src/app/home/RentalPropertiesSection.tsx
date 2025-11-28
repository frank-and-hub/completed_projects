import { Box, Center, Container, Flex, Group, Text } from "@mantine/core";
import Image from "next/image";
import Link from "next/link";
import React from "react";
export const RentalPropertiesSectionData = [
  { id: 1, name: "Gauteng", link: "/property-for-rent/gauteng" },
  { id: 2, name: "KwaZulu-Natal", link: "/property-for-rent/kwazulu-natal" },
  { id: 3, name: "Western Cape", link: "/property-for-rent/western-cape" },
  { id: 4, name: "Mpumalanga", link: "/property-for-rent/mpumalanga" },
  { id: 5, name: "Eastern Cape", link: "/property-for-rent/eastern-cape" },
];
function RentalPropertiesSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="rental_properties_container">
          <Center className="rental_properties_center">
            <div className="rental_properties_center_content">
              <h2 style={{ textAlign: "center" }}>
                Top Provinces For Rental Properties in South Africa
              </h2>
              <Flex
                direction={"row"}
                wrap={"wrap"}
                align={"center"}
                justify={"center"}
                mt={25}
              >
                {RentalPropertiesSectionData.map((item, index) => (
                  <Link href={item?.link}>
                    <Flex className="rental_properties_card" key={index}>
                      <Text fz={16} fw={500}>
                        {item?.name}
                      </Text>
                      <Box className="rental_properties_card_icon">
                        <Image
                          src={require("../../../assets/svg/home_with_tree.svg")}
                          alt={"no_image"}
                          height={35}
                          width={35}
                        />
                      </Box>
                    </Flex>
                  </Link>
                ))}
              </Flex>
            </div>
          </Center>
        </div>
      </Container>
    </section>
  );
}

export default RentalPropertiesSection;
