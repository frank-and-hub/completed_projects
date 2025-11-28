import { Container, Text } from "@mantine/core";
import React from "react";
import "./home2Sections.scss";
import CustomButton from "@/components/customButton/CustomButton";
import AuthModal from "@/app/auth/AuthModal";
import CustomModal from "@/components/customModal/CustomModal";
function Home2BannerSection() {
  return (
    <section className="main_section" id="home">
      <Container size={"xl"}>
        <div className="banner_section_content_container">
          <h1>
            Rent Your Property Online <span> No Middlemen, No Hassle!</span>
          </h1>
          <h2>
            Rent your property online with ease! Get a dedicated landlord CRM,
            WhatsApp alerts for matched tenants, and seamless booking
            management—all in one place.
          </h2>
          <CustomModal
            actionButton={
              <CustomButton bg={"#FFFF"} iconProps={{ color: "#FFF" }}>
                <Text c={"#000"}>List Your Property Now</Text>
              </CustomButton>
            }
          >
            <AuthModal type={"landlordSignUp"} />
          </CustomModal>
        </div>
      </Container>
    </section>
  );
}

export default Home2BannerSection;
