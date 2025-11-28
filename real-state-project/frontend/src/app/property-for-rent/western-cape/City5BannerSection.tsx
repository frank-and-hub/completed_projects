import { Container } from "@mantine/core";
import React from "react";
import "../citySections.scss";
function City5BannerSection() {
  return (
    <section className="main_section_city" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container_city">
          <h1>
            Discover the Best Rentals, Find Property to Rent in{" "}
            <span> Western Cape </span>
          </h1>
          <h2>Scenic Living, Urban Charm</h2>
        </div>
      </Container>
    </section>
  );
}

export default City5BannerSection;
