import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Grid, SimpleGrid, Text } from "@mantine/core";
import { IconSearch } from "@tabler/icons-react";
import Image from "next/image";
import Link from "next/link";
import HomeCard from "./components/homeCard/HomeCard";
import AdvanceFilter from "./components/advanceFilter/AdvanceFilter";
import CustomModal from "@/components/customModal/CustomModal";

const data = [
  {
    id: "1",
    title: "Set Your Preferences",
    description:
      "Select budget, location, and requirements for your ideal property to rent.",
    icon: <IconSearch size={"2.2rem"} stroke={2} />,
  },
  {
    id: "2",
    title: "Sign Up & Create Profile",
    description:
      "Register on the platform and set your preferences for a personalized experience.",
    // points: [
    //   'Get a clear overview of your needs.',
    //   ' Ensure every detail aligns perfectly.',
    // ],
    icon: (
      <Image
        src={require("../../../assets/images/headshek.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "3",
    title: "Get Notified Instantly",
    description:
      "Receive WhatsApp alerts for apartments to rent and other matching listings.",
    // points: [
    //   'Prioritize your needs with a simple payment.',
    //   'Unlock access to tailored properties.',
    // ],
    icon: (
      <Image
        src={require("../../../assets/images/chat.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "4",
    title: "Connect & Rent Easily",
    description:
      "Message landlords or agents directly—no middlemen, no unnecessary hassle.",
    // points: [
    //   'Sit back and relax as our algorithm works.',
    //   ' Receive perfect matches directly to WhatsApp.',
    // ],
    icon: (
      <Image
        src={require("../../../assets/images/bookmark.svg")}
        alt={"no_image"}
      />
    ),
  },
  {
    id: "5",
    title: "Finalize & Move In!",
    description:
      "Meet the landlord, confirm rental, and settle into your new home.",
    icon: (
      <Image
        src={require("../../../assets/images/home.svg")}
        alt={"no_image"}
      />
    ),
  },
];
function SectionTwo() {
  return (
    <section className="homeCard_sec section_Two">
      <Container size={"lg"}>
        <Flex align={"center"} justify={"center"} direction="column" pb={30}>
          <Box className="heading_box_sec">
            <p>How It Works</p>
          </Box>
          <h2>How It Works</h2>
          <h3>
            Check out our simple Step-by-Step Process for the Perfect Property
            to Rent
          </h3>
        </Flex>

        <div className="grid-parent-container mantine-Grid-root __m__-r1t">
          <div className="grid-container mantine-Grid-inner">
            <div className="left-side-container mantine-Grid-col __m__-r1v">
              <div className="get_h_wrk">
                <figcaption>
                  <h3>The new way to find your home</h3>
                  <p>
                    Effortlessly match your rental needs to available properties
                    and discover your dream home.
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
                    className="comman_modal_custom_next"
                    actionButton={
                      <CustomButton>Start Searching Now</CustomButton>
                    }
                  >
                    <AdvanceFilter isFromSearch={true} />
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

export default SectionTwo;
