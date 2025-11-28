import { Box, Center, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import React from "react";
import HomeCard from "../../../home/components/homeCard/HomeCard";
import {
  IconCalendarTime,
  IconDeviceImac,
  IconFilterDiscount,
  IconHomeSearch,
  IconListDetails,
  IconRosetteDiscountCheck,
} from "@tabler/icons-react";
import Image from "next/image";
const data = [
  {
    id: "1",
    title: "Get Instant Tenant Matches",
    description: "No waiting, get WhatsApp alerts when a match is found.",
    icon: <IconHomeSearch size={40} stroke={1.5} />,
  },
  {
    id: "2",
    title: "Verified Tenants Only",
    description: "Reduce risk with genuine tenant profiles",

    icon: <IconRosetteDiscountCheck size={40} stroke={1.5} />,
  },
  {
    id: "3",
    title: "Seamless Meeting Booking",
    description: "Book tenant viewings directly from the admin panel.",

    icon: <IconCalendarTime size={40} stroke={1.5} />,
  },
];
const data2 = [
  {
    id: "1",
    title: "Dedicated CRM for Landlords",
    description:
      "Manage your property listings, tenant matches, and booking meetings in one place.",
    icon: <IconDeviceImac stroke={1.5} size={40} />,
  },
  {
    id: "2",
    title: "Hassle-Free Listing Process",
    description: "Upload and manage your properties in minutes.",

    icon: <IconListDetails stroke={1.5} size={40} />,
  },
  {
    id: "3",
    title: "No Middlemen Fees",
    description: "Rent out directly without extra charges.",

    icon: <IconFilterDiscount stroke={1.5} size={40} />,
  },
];
function Home2WhyChooseUsSection() {
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>Why Choose Us</p>
          </Box>
          <h2>Why List Your Property on PocketProperty?</h2>
          <h3>What Makes PocketProperty the Best Choice?</h3>
        </Flex>
        <SimpleGrid cols={{ base: 1, sm: 1, lg: 3, md: 3 }}>
          <div>
            <SimpleGrid>
              {data?.map((item) => (
                <HomeCard
                  key={item?.id}
                  item={item}
                  cardProps={{ style: { border: "none", padding: "0px 15px" } }}
                />
              ))}
            </SimpleGrid>
          </div>
          <div>
            <Image
              src={require("../../../../../assets/images/why_choose_us_img.png")}
              alt="no_img"
              style={{ width: "100%", height: "100%" }}
            />
          </div>
          <div>
            <SimpleGrid>
              {data2?.map((item) => (
                <HomeCard
                  key={item?.id}
                  item={item}
                  cardProps={{ style: { border: "none", padding: "0px 15px" } }}
                />
              ))}
            </SimpleGrid>
          </div>
        </SimpleGrid>
      </Container>
    </section>
  );
}

export default Home2WhyChooseUsSection;
