import { Container } from "@mantine/core";
import React from "react";
import "../citySections.scss";
function CityBannerSection() {
  return (
    <section className="main_section_city" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container_city">
          <h1>
            Your Perfect Home Awaits, Discover Property to Rent in{" "}
            <span> Gauteng </span>
          </h1>
          <h2>South Africa’s economic hub!</h2>
        </div>
      </Container>
    </section>
  );
}

export default CityBannerSection;
