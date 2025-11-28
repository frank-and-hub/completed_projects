import CustomButton from "@/components/customButton/CustomButton";
import { Container, Group, Text } from "@mantine/core";
import Link from "next/link";
import React from "react";

function Home3ManagingPropertiesSection() {
  return (
    <section className="homeCard_sec">
      <Container size={"lg"}>
        <div className="managing_properties_container">
          <div
            style={{ width: "50%", padding: "20px" }}
            className="managing_properties_text_container"
          >
            <h2>Start Managing Your Properties the Smart Way</h2>
            <Group mt={20}>
              <Link
                href={"https://form.jotform.com/242595895839581"}
                target="_blank"
              >
                <CustomButton pl={10}>
                  <Text fz={14}>Sign Up as an Agency</Text>
                </CustomButton>
              </Link>
              <Link
                href={"https://form.jotform.com/242595895839581"}
                target="_blank"
              >
                <CustomButton iconProps={{ color: "#000" }} bg={"#000"}>
                  <Text fz={14}>Schedule a Demo</Text>
                </CustomButton>
              </Link>
            </Group>
          </div>
        </div>
      </Container>
    </section>
  );
}

export default Home3ManagingPropertiesSection;
