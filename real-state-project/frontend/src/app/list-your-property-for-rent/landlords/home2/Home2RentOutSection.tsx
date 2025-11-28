import AuthModal from "@/app/auth/AuthModal";
import CustomButton from "@/components/customButton/CustomButton";
import CustomModal from "@/components/customModal/CustomModal";
import { Container, Text } from "@mantine/core";
import React from "react";

function Home2RentOutSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="rent_out_container">
          <div
            style={{ width: "50%", padding: "20px" }}
            className="rent_out_text_container"
          >
            <h2>Ready to Rent Out Your Property?</h2>
            <CustomModal
              actionButton={
                <CustomButton mt={10}>
                  <Text fz={14}>List Your Property Now</Text>
                </CustomButton>
              }
            >
              <AuthModal type={"landlordSignUp"} />
            </CustomModal>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home2RentOutSection;
