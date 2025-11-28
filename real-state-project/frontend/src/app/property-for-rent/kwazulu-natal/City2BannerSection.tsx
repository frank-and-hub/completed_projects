import { Container } from "@mantine/core";
import React from "react";
import "../citySections.scss";
function City2BannerSection() {
  return (
    <section className="main_section_city" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container_city">
          <h1>
            Find Your Ideal Rental in <span> KwaZulu-Natal </span>
          </h1>
          <h2>Coastal Beauty, Urban Comfort!</h2>
        </div>
      </Container>
    </section>
  );
}

export default City2BannerSection;
