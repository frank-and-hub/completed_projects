import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Grid, SimpleGrid, Text } from "@mantine/core";
import Link from "next/link";
import React from "react";
import HomeCard from "../../../home/components/homeCard/HomeCard";
import { IconMapPins, IconSearch } from "@tabler/icons-react";
import Image from "next/image";
import CustomModal from "@/components/customModal/CustomModal";
import AuthModal from "@/app/auth/AuthModal";
const data = [
  {
    id: "1",
    title: "Sign Up & List Your Property",
    description:
      "Add property details, upload images, and set rental terms effortlessly. List your property for rent in just a few clicks.",
    icon: (
      <Image
        src={require("../../../../../assets/images/home.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "2",
    title: "Get Instant Tenant Matches",
    description:
      "Receive WhatsApp notifications when tenants interested in renting your property online are found.",

    icon: (
      <Image
        src={require("../../../../../assets/images/headshek.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "3",
    title: "Book a Property Viewing",
    description:
      "Log in to your landlord panel and schedule a tenant visit easily.",

    icon: (
      <Image
        src={require("../../../../../assets/images/chat.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "4",
    title: "Tenant Receives Confirmation",
    description: "Tenants get automated WhatsApp updates with meeting details.",

    icon: (
      <Image
        src={require("../../../../../assets/images/bookmark.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "5",
    title: "Finalize the Rental Agreement",
    description:
      "Rent your house online seamlessly—meet the tenant, sign the agreement, and close the deal!",
    icon: <IconMapPins stroke={2} size={45} />,
  },
];
function Home2HowItWorkSection() {
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
                    Listing your property is a breeze with PocketProperty!
                  </h3>
                  <p>
                    Save time and hassle! List your property effortlessly with
                    PocketProperty.
                  </p>
                  <Link href="https://linktr.ee/PocketProperty" target="_blank">
                    <CustomButton>How İt Works</CustomButton>
                  </Link>
                </figcaption>
              </div>
            </div>
            <div className="right-side-container mantine-Grid-col __m__-r24">
              <SimpleGrid cols={{ base: 1, sm: 2, lg: 2 }}>
                {data?.map((item) => (
                  <HomeCard key={item?.id} item={item} isShowFigures={true} />
                ))}
                <Box className="start_searching_button_container">
                  <CustomModal
                    actionButton={
                      <CustomButton>
                        <Text>Start Listing Now</Text>
                      </CustomButton>
                    }
                  >
                    <AuthModal type={"landlordSignUp"} />
                  </CustomModal>
                </Box>
              </SimpleGrid>
            </div>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home2HowItWorkSection;
