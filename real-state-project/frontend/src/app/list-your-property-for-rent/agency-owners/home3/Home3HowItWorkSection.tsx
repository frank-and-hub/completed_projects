import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Grid, SimpleGrid, Text } from "@mantine/core";
import Link from "next/link";
import React from "react";
import {
  IconArrowNarrowUp,
  IconBell,
  IconDeviceDesktopAnalytics,
  IconListCheck,
  IconMapPins,
  IconUserSearch,
} from "@tabler/icons-react";
import Image from "next/image";
import HomeCard from "@/app/home/components/homeCard/HomeCard";
const data = [
  {
    id: "1",
    title: "Register & Set Up Your Agency Profile",
    description: "List multiple properties in one place",
    icon: <IconUserSearch stroke={1.5} size={40} />,
  },
  {
    id: "2",
    title: "Automated Matching & Notifications",
    description: "Get notified via WhatsApp when tenants match your listings.",

    icon: <IconBell stroke={1.5} size={40} />,
  },
  {
    id: "3",
    title: "Schedule & Manage Viewings Efficiently",
    description: "Use the CRM to book meetings and track visits.",

    icon: <IconListCheck stroke={1.5} size={40} />,
  },
  {
    id: "4",
    title: "Track Leads & Tenant Interactions",
    description: "Manage inquiries and track tenant interest.",

    icon: <IconDeviceDesktopAnalytics stroke={1.5} size={40} />,
  },
  {
    id: "5",
    title: "Close Deals Faster",
    description: "Simplify property handovers with a structured workflow",
    icon: (
      <Image
        src={require("../../../../../assets/svg/note_pencil.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "6",
    title: "Streamline Property Management",
    description: "Boost efficiency with PocketProperty",
    icon: (
      <Box
        style={{
          backgroundColor: "#f30051",
          borderRadius: "50%",
          height: 30,
          width: 30,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          marginLeft: 10,
        }}
      >
        <IconArrowNarrowUp
          stroke={1.5}
          color="#FFF"
          style={{
            transform: "rotate(45deg)",
          }}
        />
      </Box>
    ),
    link: "https://form.jotform.com/242595895839581",
  },
];
function Home3HowItWorkSection() {
  return (
    <section className="homeCard_sec section_Two">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>How It Works</p>
          </Box>
          <h2>How It Works</h2>
          <h3>Check out our simple Step-by-Step Process</h3>
        </Flex>
        <div className="grid-parent-container mantine-Grid-root __m__-r1t">
          <div className="grid-container mantine-Grid-inner">
            <div className="left-side-container mantine-Grid-col __m__-r1v">
              <div className="get_h_wrk">
                <figcaption>
                  <h3>
                    Streamline Your Operations. Try PocketProperty for Agencies.
                  </h3>
                  <p>The Agency Solution You've Been Waiting For!</p>
                  <Link href="https://linktr.ee/PocketProperty" target="_blank">
                    <CustomButton>How It Works</CustomButton>
                  </Link>
                </figcaption>
              </div>
            </div>
            <div className="right-side-container mantine-Grid-col __m__-r24">
              <SimpleGrid cols={{ base: 1, sm: 2, lg: 2 }}>
                {data?.map((item) => (
                  <HomeCard key={item?.id} item={item} isShowFigures={true} />
                ))}
                {/* <Box className="start_searching_button_container">
                  <Link
                    href={"https://form.jotform.com/242595895839581"}
                    target="_blank"
                  >
                    <CustomButton>
                      <Text>Try PocketProperty for Agencies</Text>
                    </CustomButton>
                  </Link>
                </Box> */}
              </SimpleGrid>
            </div>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home3HowItWorkSection;
