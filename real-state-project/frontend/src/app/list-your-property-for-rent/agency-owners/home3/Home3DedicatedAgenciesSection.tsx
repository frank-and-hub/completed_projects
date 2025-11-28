import LandlordsCard from "../../landlords/home2/components/landlordsCard/LandlordsCard";
import { Container, Flex, SimpleGrid } from "@mantine/core";
import Image from "next/image";
import React from "react";
import "./home3Sections.scss";

const data = [
  {
    id: 1,
    title: "Centralized Property Management",
    value: "List & manage multiple properties from one place.",
  },
  {
    id: 2,
    title: "Lead Tracking & Analytics",
    value: "Understand which listings get the most interest.",
  },
  {
    id: 3,
    title: "Automated Meeting Scheduling",
    value: "Easily book & manage property viewings.",
  },
  {
    id: 4,
    title: "New Tenant Alerts Inbox",
    value: "You can see all matches of Tenants in CRM",
  },
  {
    id: 5,
    title: "Instant Notifications",
    value: "Get WhatsApp alerts for new tenant matches.",
  },
];
function Home3DedicatedAgenciesSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="grid-container-maine">
          <div>
            <h2 style={{ textTransform: "uppercase" }}>
              Dedicated CRM for Agencies
            </h2>
          </div>
          <SimpleGrid mt={20} cols={{ base: 1, sm: 2, lg: 2 }}>
            <SimpleGrid mt={20} cols={{ base: 1, sm: 2, lg: 2 }}>
              {data?.map((item, index) => (
                <LandlordsCard item={item} />
              ))}
            </SimpleGrid>
            <Flex mt={20} justify={"center"}>
              <Image
                src={require("../../../../../assets/images/dedicated_agencies.png")}
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

export default Home3DedicatedAgenciesSection;
