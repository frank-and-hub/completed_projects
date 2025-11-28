import { Box, Container, Flex, Grid, SimpleGrid } from "@mantine/core";
import React from "react";
import ClientReviewCard from "./components/clientReviewCard/ClientReviewCard";
const data = [
  {
    id: 1,
    useName: "Thabo Mokoena ",
    location: "Johannesburg, Gauteng",
    message:
      "As a landlord, finding the right tenant used to be time-consuming, but PocketProperty has made it seamless. The platform connects me with verified tenants in South Africa, and the WhatsApp property matching feature is a game-changer. I also love the built-in contract system—it saves me a lot of paperwork. Highly recommended for any landlord looking for a hassle-free rental experience!",
  },
  {
    id: 2,
    useName: "Ayanda Dlamini",
    location: "Cape Town, Western Cape",
    message:
      "I used to rely on traditional listings to rent out my apartments, but PocketProperty is on another level. The automated property matching saves time, and I get tenant leads instantly on WhatsApp. I also appreciate how easy it is to communicate and finalize agreements through the platform. If you're a landlord in South Africa, this is the best tool to manage rentals efficiently!",
  },
  {
    id: 1,
    useName: "Kirsten de Villiers",
    location: "Port Elizabeth (Gqeberha), Eastern Cape",
    message:
      "I never thought renting out properties could be this easy! With PocketProperty, I don’t have to chase tenants or waste time with unresponsive leads. The system finds the right tenants, lets them reach out instantly, and even helps me set up meetings—all in one place. Plus, I can draft contracts online without dealing with paperwork. If you're a landlord in South Africa, give it a try—you won’t regret it!",
  },
  {
    id: 1,
    useName: "Michael Botha",
    location: "Pretoria, Gauteng",
    message:
      "I’ve been a landlord for years, and PocketProperty is the best rental platform I’ve used. It automatically connects me with tenants, sends property matches via WhatsApp, and lets me book meetings effortlessly. The built-in contract feature is a huge bonus. No more hassle—just quick and easy rentals!",
  },
];
function Home2OurClientSection({
  reviews,
  isTenants,
}: {
  reviews?: any;
  isTenants?: boolean;
}) {
  return (
    <section className="homeCard_sec section_Two">
      <Container size={"lg"}>
        <div className="grid-container">
          <div className="left-side-container ">
            <Flex className="heading_box_flex">
              <Box className="heading_box_sec">
                <p>What Our {isTenants ? "Tenants" : "Clients"} Say</p>
              </Box>
            </Flex>
            <h2
              className="heading2"
              style={{ textTransform: "uppercase", textAlign: "left" }}
            >
              Testimonials & Reviews
            </h2>
            <h3 className="heading3" style={{ textAlign: "left" }}>
              Our {isTenants ? "Tenants'" : "clients’"} success stories reflect
              our commitment to excellence. See how we’ve helped them find their
              dream homes.
            </h3>
          </div>
          <div className="right-side-container">
            <SimpleGrid cols={{ base: 1, sm: 2, lg: 2 }}>
              {(reviews ?? data)?.map((item: any) => (
                <ClientReviewCard item={item} key={item?.id} />
              ))}
            </SimpleGrid>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home2OurClientSection;
