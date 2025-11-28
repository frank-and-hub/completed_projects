import CustomButton from "@/components/customButton/CustomButton";
import { Container, Group, Text } from "@mantine/core";
import React from "react";
import "./home3Sections.scss";
import Link from "next/link";
function Home3BannerSection() {
  return (
    <section className="main_section" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container">
          <h1>
            Streamline Agency’s Rentals{" "}
            {/* Streamline Your Agency’s Rentals with Pocket Property, */}
            <span>with PocketProperty</span>
          </h1>
          <h2>
            Scale your agency’s rental business with automated tenant matching,
            seamless meeting bookings, and a dedicated CRM
          </h2>
          <Group>
            <Link
              href={"https://form.jotform.com/242595895839581"}
              target="_blank"
            >
              <CustomButton bg={"#FFFF"} iconProps={{ color: "#FFF" }}>
                <Text c={"#000"}>Start Managing Properties</Text>
              </CustomButton>
            </Link>
            {/* <Link
              href={"https://form.jotform.com/242595895839581"}
              target="_blank"
            >
              <CustomButton iconContainerBoxProps={{ bg: "#FFFF" }}>
                <Text>Learn More</Text>
              </CustomButton>
            </Link> */}
          </Group>
        </div>
      </Container>
    </section>
  );
}

export default Home3BannerSection;
