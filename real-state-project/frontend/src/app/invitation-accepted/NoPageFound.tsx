"use client";

import React from "react";
import "./InvitationAcceptThankyou.scss";
import Lottie from "lottie-react";
import { Box, Center, Container, Paper, Title } from "@mantine/core";
// import NoPageFoundLottie from "./no-page-found.json";
import NoLottie from "./404.json";
import CustomText from "@/components/customText/CustomText";
import CustomButton from "@/components/customButton/CustomButton";
import { useRouter } from "next/navigation";

function NoPageFound() {
  const route = useRouter();
  return (
    <Box bg={"#f30051"}>
      <section className="main_section ">
        <Container>
          <Paper className="content_box" shadow="md" radius={"md"} pt="0">
            <Lottie
              animationData={NoLottie}
              loop={true}
              style={{ height: "50vh" }}
            />

            <Box mt="-5vh">
              <Title order={5} ta="center" size={"lg"} px="xl">
                Oops! The page you're looking for doesn't exist.
              </Title>
              <Center mt="md" onClick={() => route.replace("/")}>
                <CustomButton>Home page</CustomButton>
              </Center>
            </Box>
          </Paper>
        </Container>
      </section>
    </Box>
  );
}

export default NoPageFound;
