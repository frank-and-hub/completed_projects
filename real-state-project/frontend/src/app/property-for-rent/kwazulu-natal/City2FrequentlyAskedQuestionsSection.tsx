import React from "react";
import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Text } from "@mantine/core";
import Link from "next/link";
import JsonLdScript from "../../JsonLdScript";
import { text } from "stream/consumers";
const faqData = [
  {
    value: "Where can I find affordable property to rent in KwaZulu-Natal?",
    description:
      "KwaZulu-Natal offers a variety of affordable rental properties, with budget-friendly options available in Pietermaritzburg, Richards Bay, and certain areas of Durban. If you're looking for flats to rent in Durban, consider suburbs like Umbilo or Pinetown for cost-effective choices.",
  },
  {
    value: "What are the best areas to rent apartments in Durban??",
    description: (
      <p>
        <b>
          The best areas for apartments to rent in Durban depend on your
          lifestyle:
        </b>
        <br />
        <b> Beachfront living:</b> Umhlanga, North Beach, and Morningside.
        <br />
        <b>Affordable city rentals:</b> Glenwood, Pinetown, and Umbilo.
        <br />
        <b> Luxury apartments:</b> Durban North and La Lucia.
      </p>
    ),
  },
  {
    value: "Is Ballito a good place to rent a house?",
    description: (
      <p>
        <b>
          Yes! Houses to rent in Ballito are highly sought-after, especially for
          families and professionals seeking a coastal lifestyle. Ballito
          offers:
        </b>
        <br />
        Secure gated estates with modern amenities.
        <br />
        Close proximity to top-rated schools and beaches.
        <br />A peaceful, community-focused environment with easy access to
        Durban.
      </p>
    ),
  },

  {
    value: "How much does it cost to rent a flat in Richards Bay?",
    description: (
      <p>
        <b>
          The cost of flats to rent in Richards Bay varies based on size and
          location:
        </b>
        <br />
        <b> 1-bedroom flats:</b> ZAR 4,500 – ZAR 7,500 per month.
        <br />
        <b> 2-bedroom flats:</b> ZAR 6,000 – ZAR 10,000 per month.
        <br />
        <b> Luxury apartments:</b> Higher-end units range from ZAR 12,000+.
      </p>
    ),
  },

  {
    value:
      "What should I consider when looking for accommodation to rent in Pietermaritzburg?",
    description: (
      <p>
        <b>
          When searching for accommodation to rent in Pietermaritzburg,
          consider:
        </b>
        <br />
        Proximity to universities and workplaces.
        <br />
        Security and neighborhood safety.
        <br />
        Affordability, as Pietermaritzburg offers many budget-friendly rentals.
        <br />
        Lease agreements and deposit requirements before signing.
      </p>
    ),
  },
];

function City2FrequentlyAskedQuestionsSection() {
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    // name: "Featured Rental Properties",
    // itemListElement: properties.map((property, index) => ({
    mainEntity: [
      {
        "@type": "Question",
        name: "Where can I find affordable property to rent in KwaZulu-Natal?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[0].description,
        },
      },
      {
        "@type": "Question",
        name: "What are the best areas to rent apartments in Durban?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[1].description,
        },
      },
      {
        "@type": "Question",
        name: "Is Ballito a good place to rent a house?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[2].description,
        },
      },

      {
        "@type": "Question",
        name: "How much does it cost to rent a flat in Richards Bay?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[3].description,
        },
      },

      {
        "@type": "Question",
        name: "What should I consider when looking for accommodation to rent in Pietermaritzburg?",
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

export default City2FrequentlyAskedQuestionsSection;
