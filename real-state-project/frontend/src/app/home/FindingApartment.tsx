import CustomButton from "@/components/customButton/CustomButton";
import CustomModal from "@/components/customModal/CustomModal";
import { Container, Group, Text } from "@mantine/core";
import React from "react";
import AdvanceFilter from "./components/advanceFilter/AdvanceFilter";
import AuthModal from "../auth/AuthModal";

function FindingApartment() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="finding_apartment_container">
          <div
            style={{ padding: "20px" }}
            className="finding_apartment_text_container"
          >
            <h2 style={{ textTransform: "uppercase" }}>
              Start Finding Your Dream <br /> Apartment Today!
            </h2>
            <Group mt={20}>
              <CustomModal
                actionButton={
                  <CustomButton
                    bg={"#000"}
                    pl={10}
                    iconProps={{ color: "#000" }}
                  >
                    Sign Up Now
                  </CustomButton>
                }
              >
                <AuthModal type="signup" />
              </CustomModal>

              <CustomModal
                className="comman_modal_custom_next"
                actionButton={<CustomButton>Start Searching Now</CustomButton>}
              >
                <AdvanceFilter isFromSearch={true} />
              </CustomModal>
            </Group>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default FindingApartment;
