import { Box, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import React from "react";
import Image from "next/image";
import {
  IconCalendarTime,
  IconDeviceImac,
  IconFilterDiscount,
  IconHomeSearch,
  IconListDetails,
  IconSettingsAutomation,
} from "@tabler/icons-react";
import HomeCard from "@/app/home/components/homeCard/HomeCard";
const data = [
  {
    id: "1",
    title: "Manage Multiple Properties Easily",
    description: "Handle multiple rental listings from a single dashboard.",
    icon: <IconHomeSearch size={40} stroke={1.5} />,
  },
  {
    id: "2",
    title: "Automated Tenant Matching",
    description:
      "Save time with instant WhatsApp alerts for potential tenants.",

    icon: <IconSettingsAutomation stroke={1.5} size={40} />,
  },
  {
    id: "3",
    title: "Smart Meeting Scheduling",
    description: "Avoid back-and-forth emails with automated meeting bookings.",

    icon: <IconCalendarTime size={40} stroke={1.5} />,
  },
];
const data2 = [
  {
    id: "1",
    title: "CRM & Analytics for Agencies",
    description:
      "Track property views, inquiries, and engagement in real time.",
    icon: <IconDeviceImac stroke={1.5} size={40} />,
  },
  {
    id: "2",
    title: "Faster Tenant Acquisition",
    description: "Reduce vacancy rates by connecting with tenants quickly.",

    icon: <IconListDetails stroke={1.5} size={40} />,
  },
  {
    id: "3",
    title: "No Hidden Fees",
    description:
      "Enjoy cost-effective lead generation without expensive commissions.",

    icon: <IconFilterDiscount stroke={1.5} size={40} />,
  },
];
function Home3WhyChooseUsSection() {
  return (
    <section className="homeCard_sec" id="features">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>Why Choose Uss</p>
          </Box>
          <h2>Why Agencies Love PocketProperty?</h2>
          <h3>What Makes PocketProperty the Best Choice for agencies?</h3>
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
              src={require("../../../../../assets/images/why_choose_us_img_home3.png")}
              alt="Why choose PocketProperty"
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

export default Home3WhyChooseUsSection;
