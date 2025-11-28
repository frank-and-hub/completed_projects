import { Container } from "@mantine/core";
import React from "react";
import "../citySections.scss";
function City3BannerSection() {
  return (
    <section className="main_section_city" id="home">
      <Container size={"lg"}>
        <div className="banner_section_content_container_city">
          <h1>
            From City to Coast – Find Your Rental in <span> Eastern Cape </span>
          </h1>
          <h2>Heritage & Coastline</h2>
        </div>
      </Container>
    </section>
  );
}

export default City3BannerSection;
