import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Text } from "@mantine/core";
import JsonLdScript from "../../JsonLdScript";
import Link from "next/link";
import React from "react";
const faqData = [
  {
    value: "Where can I find cheap apartments for rent in Gauteng?",
    description:
      "You can find cheap apartments for rent in areas like Johannesburg, Boksburg, and Randburg, where rental prices are more affordable compared to Sandton or Midrand. These areas offer a variety of budget-friendly flats and 1-bedroom apartments suitable for students, professionals, and families.",
  },
  {
    value: "What are the best areas to rent apartments in Gauteng?",
    description: (
      <>
        <p>
          <b>The best areas depend on your needs:</b>
          <br />
          <b>Midrand –</b> Ideal for professionals, with modern apartments and
          easy Gautrain access.
          <br />
          <b>Randburg –</b> Affordable and family-friendly, with great
          amenities.
          <br />
          <b>Sandton –</b> Luxury apartments near major business hubs.
          <br />
          <b>Johannesburg –</b> Budget-friendly flats for city living.
          <br />
          <b>Boksburg –</b> A quieter suburban area with affordable rentals.
        </p>
      </>
    ),
  },
  {
    value: "How much is the average rent for a 1-bedroom apartment in Gauteng?",
    description: (
      <>
        <p>
          <b>
            The rent for a 1-bedroom apartment for rent in Gauteng varies by
            location:
          </b>
          <br />
          <b> Johannesburg:</b> ZAR 3,500 – ZAR 6,000 per month
          <br />
          <b>Midrand:</b> ZAR 5,000 – ZAR 8,500 per month
          <br />
          <b> Randburg:</b> ZAR 4,500 – ZAR 7,500 per month
          <br />
          <b>Sandton:</b> ZAR 8,000 – ZAR 15,000 per month
        </p>
      </>
    ),
  },

  {
    value: "What should I consider before renting a property in Gauteng?",
    description: (
      <>
        <p>
          <b>Before renting, consider:</b>
          <br />
          <b> Location & Transport –</b>Proximity to work, schools, and public
          transport.
          <br />
          <b> Security & Amenities – </b>Gated communities, parking, and 24/7
          security.
          <br />
          <b> Rental Costs & Deposits –</b>Check affordability and lease
          agreement terms.
          <br />
          <b> Landlord & Property Condition –</b>Inspect the property before
          signing.
        </p>
      </>
    ),
  },

  {
    value:
      "What is the easiest way to find apartments and flats for rent in Gauteng?",
    description:
      "The easiest way to find apartments and flats for rent in Gauteng is by using a trusted rental platform like PocketProperty, where you can submit your request based on location, budget, and property type. Compare rental prices, check tenant reviews, and book viewings hassle-free to find your ideal home.",
  },
];
function CityFrequentlyAskedQuestionsSection() {
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    // name: "Featured Rental Properties",
    // itemListElement: properties.map((property, index) => ({
    mainEntity: [
      {
        "@type": "Question",
        name: "Where can I find cheap apartments for rent in Gauteng?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[0].description,
        },
      },
      {
        "@type": "Question",
        name: "What are the best areas to rent apartments in Gauteng?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[1].description,
        },
      },
      {
        "@type": "Question",
        name: "How much is the average rent for a 1-bedroom apartment in Gauteng?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[2].description,
        },
      },
      {
        "@type": "Question",
        name: "What should I consider before renting a property in Gauteng?",
        acceptedAnswer: {
          "@type": "Answer",
          text: faqData[3].description,
        },
      },
      {
        "@type": "Question",
        name: "What is the easiest way to find apartments and flats for rent in Gauteng?",
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

export default CityFrequentlyAskedQuestionsSection;
