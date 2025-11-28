import { Box, Center, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import { IconSearch } from "@tabler/icons-react";
import React from "react";
import HomeCard from "./components/homeCard/HomeCard";
import Image from "next/image";
function WhoWeAreSection() {
  const data = [
    {
      id: "01",
      title: "Smart Search for the Perfect Home",
      description:
        "Easily find studio apartments, private property to rent, or family-friendly houses for rent using location-based search filters, budget preferences, and property type selection.",
      icon: (
        <Image
          src={require("../../../assets/svg/whoWeAre1.svg")}
          alt={"no_image"}
          height={35}
          width={35}
        />
      ),
    },
    {
      id: "02",
      title: "Verified Listings, No Rental Scams",
      description:
        "All properties to rent are screened for authenticity, ensuring you only deal with trusted landlords and agents—no fake listings, no hidden surprises.",
      icon: (
        <Image
          src={require("../../../assets/svg/whoWeAre2.svg")}
          alt={"no_image"}
          height={35}
          width={35}
        />
      ),
    },
    {
      id: "03",
      title: "Instant WhatsApp Alerts for Rentals",
      description:
        "Stop endlessly searching! Get real-time notifications for apartments to rent, houses for rent, or private property to rent the moment they match your preferences.",
      icon: (
        <Image
          src={require("../../../assets/svg/whoWeAre3.svg")}
          alt={"no_image"}
          height={35}
          width={35}
        />
      ),
    },
    {
      id: "04",
      title: "Direct Contact with Landlords & Agents",
      description:
        "No middlemen, no unnecessary fees. Message landlords directly and book your dream apartment for rent or house for rent hassle-free.",
      icon: (
        <Image
          src={require("../../../assets/svg/whoWeAre4.svg")}
          alt={"no_image"}
          height={35}
          width={35}
        />
      ),
    },
  ];
  return (
    <section className="homeCard_sec section_Two" id="features">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>Who We Are</p>
          </Box>
          <h2>Why Choose PocketProperty?</h2>
          <h3>The Best Features to Simplify Your Rental Search</h3>
        </Flex>
        <div className="grid-container">
          <div className="right-side-container">
            <SimpleGrid cols={2}>
              {data?.map((item) => (
                <HomeCard
                  key={item?.id}
                  item={item}
                  groupStyle={{ mt: 0, mb: 0 }}
                />
              ))}
            </SimpleGrid>
          </div>
          <div className="left-side-container ">
            <Center>
              <Image
                className="who_we_section_img"
                src={require("../../../assets/images/choose-pocket-property.png")}
                alt={"House to rent in South Africa"}
                style={{ height: "100%" }}
              />
            </Center>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default WhoWeAreSection;
