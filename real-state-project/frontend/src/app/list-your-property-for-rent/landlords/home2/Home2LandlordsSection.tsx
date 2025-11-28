import { Center, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import Image from "next/image";
import React from "react";
import LandlordsCard from "./components/landlordsCard/LandlordsCard";
const data = [
  {
    id: 1,
    title: "Smart Matching System",
    value: "Receive notifications for the most suitable tenants.",
  },
  {
    id: 2,
    title: "Verified Tenant Profiles",
    value: "Find genuine tenants with confidence.",
  },
  {
    id: 3,
    title: "Easy Meeting Scheduling",
    value: "Set up property viewings with just a few clicks.",
  },
  {
    id: 4,
    title: "Automated WhatsApp Updates",
    value: "Get notified instantly about new matches.",
  },
  {
    id: 5,
    title: "Dynamic Contract",
    value: "Get notified instantly about new matches.",
  },
];
function Home2LandlordsSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <h2 style={{ textTransform: "uppercase" }}>
            Best Features for Landlords
          </h2>
          <SimpleGrid mt={20} cols={{ base: 1, sm: 2, lg: 2 }}>
            <SimpleGrid mt={20} cols={{ base: 1, sm: 2, lg: 2 }}>
              {data?.map((item, index) => (
                <LandlordsCard item={item} />
              ))}
            </SimpleGrid>
            <Flex mt={20} justify={"center"}>
              <Image
                src={require("../../../../../assets/images/landlords_img.png")}
                alt="no_img"
                style={{ height: "100%" }}
              />
            </Flex>
          </SimpleGrid>
        </div>
      </Container>
    </section>
  );
}

export default Home2LandlordsSection;
