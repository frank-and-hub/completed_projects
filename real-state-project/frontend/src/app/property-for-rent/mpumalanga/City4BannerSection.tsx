import { Container } from "@mantine/core";
import React from "react";
import "../citySections.scss";
function City4BannerSection() {
  return (
    <section className="main_section_city" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container_city">
          <h1>
            Find Home, Live Easy – Rentals in <span> Mpumalanga </span>
          </h1>
          <h2>Nature, Growth, Opportunity</h2>
        </div>
      </Container>
    </section>
  );
}

export default City4BannerSection;
