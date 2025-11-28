import React from "react";
import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
//import { faqData } from '@/app/landlord/home2/Home2FrequentlyAskedQuestionsSection'
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Text } from "@mantine/core";
import Link from "next/link";
import JsonLdScript from "../../JsonLdScript";

const faqData = [
  {
    value: "Where can I find affordable apartments to rent in Cape Town?",
    description:
      "You can find affordable apartments to rent in Cape Town in areas like Observatory, Woodstock, and Maitland. If you’re looking for more budget-friendly options, consider suburbs like Bellville and Parow, which offer lower rental prices while still being close to the city center.",
  },
  {
    value: "Is Stellenbosch a good place to rent a flat?",
    description:
      "Yes! Flats to rent in Stellenbosch are in high demand, especially among students and young professionals. The town offers a mix of modern apartments and historic homes, with easy access to Stellenbosch University, shopping centers, and the famous Cape Winelands.",
  },
  {
    value: "What are the best areas to rent a house in Western Cape?",
    description: (
      <p>
        <b>If you’re looking for houses to rent in Western Cape, consider:</b>
        <br />
        <b> Paarl -</b> Spacious family homes with scenic mountain views.
        <br />
        <b>Somerset West -</b> Ideal for a relaxed, suburban lifestyle.
        <br />
        <b> George -</b> Coastal houses with a peaceful environment.
        <br />
        <b>Constantia -</b> Luxury homes surrounded by vineyards.
      </p>
    ),
  },
  {
    value: "How much does it cost to rent a 2-bedroom apartment in Cape Town?",
    description: (
      <p>
        Rental prices for <b> 2-bedroom apartments to rent in Cape Town </b>vary
        depending on the area:
        <br />
        <b> Cape Town & Waterfront -</b> ZAR 15,000 – ZAR 30,000 per month.
        <br />
        <b> Sea Point & Green Point -</b> ZAR 12,000 – ZAR 25,000 per month.
        <br />
        <b> Southern Suburbs (Claremont, Rondebosch) -</b> ZAR 10,000 – ZAR
        18,000 per month.
        <br />
        <b> More Affordable Areas (Bellville, Parow, Goodwood) -</b> ZAR 7,000 –
        ZAR 12,000 per month.
      </p>
    ),
  },

  {
    value: "What should I consider before renting a property in Western Cape?",
    description: (
      <p>
        <b>Before renting, consider:</b>
        <br />
        <b> Location & Transport –</b>Proximity to work, schools, and public
        transport.
        <br />
        <b> Security & Safety – </b>Check crime rates and building security
        features.
        <br />
        <b> Rental Budget –</b>Compare prices in different areas to find the
        best deal.
        <br />
        <b> Lease Terms –</b>Understand deposit requirements, maintenance
        responsibilities, and contract conditions.
      </p>
    ),
  },
];

function City5FrequentlyAskedQuestionsSection() {
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    // name: "Featured Rental Properties",
    // itemListElement: properties.map((property, index) => ({
    mainEntity: [
      {
        "@type": "Question",
        name: faqData[0].value,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[0].description,
        },
      },
      {
        "@type": "Question",
        name: faqData[1].value,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[1].description,
        },
      },
      {
        "@type": "Question",
        name: faqData[2].value,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[2].description,
        },
      },
      {
        "@type": "Question",
        name: faqData[3].value,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[3].description,
        },
      },
      {
        "@type": "Question",
        name: faqData[4].value,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[4].description,
        },
      },
    ],
  };
  return (
    <>
      <JsonLdScript schemaData={itemListSchema} />
      <section className="homeCard_sec section_Two">
        <Container size={"lg"}>
          <div className="grid-container">
            <div className="left-side-container ">
              <Flex className="heading_box_flex">
                <Box className="heading_box_sec">
                  <p>Help Center</p>
                </Box>
              </Flex>
              <h2
                className="heading2"
                style={{ textTransform: "uppercase", textAlign: "left" }}
              >
                Frequently asked questions
              </h2>
            </div>

            <div className="right-side-container">
              <FrequentlyAskedQuestions data={faqData} />
              <Flex
                justify={"space-between"}
                mt={20}
                pl={10}
                align={"center"}
                className="still_have_qus_container"
              >
                <Flex className="still_have_qus">
                  <span
                    style={{
                      fontSize: "18px ",
                      fontWeight: 600,
                      color: "#f30051",
                    }}
                  >
                    Still have questions?{" "}
                  </span>
                  <p
                    style={{
                      fontSize: "18px ",
                      fontWeight: 600,
                      color: "#2C2C2C",
                      marginLeft: "3px",
                    }}
                  >
                    Chat with us on WhatsApp!
                  </p>
                </Flex>
                <Link
                  href={"https://api.whatsapp.com/send?phone=+27 79 338 9178"}
                  target={"_blank"}
                >
                  <CustomButton>
                    <Text fz={15}>Chat Now</Text>
                  </CustomButton>
                </Link>
              </Flex>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}

export default City5FrequentlyAskedQuestionsSection;
