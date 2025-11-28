// SectionFive.tsx
"use client";
import React from "react";
import { Grid } from "@mantine/core";
import { Container } from "@mantine/core";
import ContactUsCard from "./components/contactUsCard/ContactUsCard";
import ContactDetailCard from "./components/contactDetailCard/ContactDetailCard"; // Correct import
interface SectionFiveProps {
  title: string;
  description: string;
}

function SectionFive() {
  return (
    <section className="contact_sec" id="contact-us">
      <Container size={"lg"}>
        <Grid>
          <Grid.Col span={6}>
            <ContactDetailCard /> {/* Correct component name */}
          </Grid.Col>
          <Grid.Col span={6}>
            <ContactUsCard />
          </Grid.Col>
        </Grid>
      </Container>
    </section>
  );
}

export default SectionFive;
