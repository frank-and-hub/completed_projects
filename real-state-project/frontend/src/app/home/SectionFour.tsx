import { Container, rem } from "@mantine/core";
import React from "react";
import FeatureCard from "./components/featureCard/FeatureCard";
import { Carousel } from "@mantine/carousel";
import { IconArrowLeft, IconArrowRight } from "@tabler/icons-react";
import useFeatureCard from "./components/featureCard/useFeatureCard";

function SectionFour() {
  const { data } = useFeatureCard();
  const slides = data?.data?.map((item) => (
    <Carousel.Slide key={item?.id}>
      <FeatureCard item={item} key={item?.id} />
    </Carousel.Slide>
  ));
  return (
    <section className="feature_bg_sec" id="features">
      <Container size={"lg"}>
        <div className="title_card_sc">
          <h2>Best Features</h2>
          <p>
            Introducing our suite of tenant-focused features, designed to
            revolutionize your rental experience:
          </p>
        </div>
        <Carousel
          className="indicators_feature"
          withIndicators
          dragFree
          slideGap="md"
          align="start"
          nextControlIcon={
            <IconArrowRight style={{ width: rem(20), height: rem(20) }} />
          }
          previousControlIcon={
            <IconArrowLeft style={{ width: rem(20), height: rem(20) }} />
          }
        >
          {slides}
        </Carousel>
      </Container>
    </section>
  );
}

export default SectionFour;
