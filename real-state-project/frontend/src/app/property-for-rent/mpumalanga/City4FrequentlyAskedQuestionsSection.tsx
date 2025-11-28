import React from "react";
import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
//import { faqData } from "@/app/landlord/home2/Home2FrequentlyAskedQuestionsSection";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Text } from "@mantine/core";
import Link from "next/link";
import JsonLdScript from "../../JsonLdScript";

const faqData = [
  {
    value: "Where can I find affordable property for rent in Mpumalanga?",
    description:
      "Affordable property for rent in Mpumalanga can be found in areas like Middelburg, Secunda, and eMalahleni (Witbank). These towns offer budget-friendly flats and houses while providing good access to schools, businesses, and public transport.",
  },
  {
    value: "What are the best areas to rent a house in Mpumalanga?",

    description: (
      <p>
        <b>The best areas depend on your needs:</b>
        <br />
        <b>Nelspruit (Mbombela) –</b> Ideal for professionals and families, with
        modern homes and great amenities.
        <br />
        <b>Witbank (eMalahleni) –</b> A top choice for those working in the
        mining and energy sectors.
        <br />
        <b>Middelburg –</b> A quiet, family-friendly town with spacious homes.
        <br />
        <b>White River –</b> Perfect for nature lovers and retirees seeking a
        peaceful environment.
      </p>
    ),
  },
  {
    value: "Is Nelspruit a good place to rent a house?",
    description: (
      <p>
        Yes! <b>Houses to rent in Nelspruit </b>are popular due to:
        <br />
        <b>Its growing business and economic opportunities.</b>
        <br />
        <b> A pleasant climate and scenic surroundings.</b>
        <br />
        <b>Access to top shopping malls, schools, and healthcare facilities.</b>
        <br />
      </p>
    ),
  },
  {
    value: "How much does it cost to rent a flat in Secunda?",
    description: (
      <p>
        Rental prices for <b> flats to rent in Secunda </b>vary depending on the
        area:
        <br />
        <b> 1-bedroom flats -</b> ZAR 4,000 – ZAR 6,500 per month.
        <br />
        <b> 2-bedroom flats -</b> ZAR 5,500 – ZAR 9,000 per month.
        <br />
        <b> Luxury apartments -</b> Can go up to ZAR 12,000+ in premium areas.
      </p>
    ),
  },

  {
    value: "What should I consider before renting a property in Mpumalanga??",
    description: (
      <p>
        <b>Before renting, consider:</b>
        <br />
        <b> Proximity to work & schools –</b>Choose a location that suits your
        daily needs.
        <br />
        <b> Budget & Rental Costs – </b>Compare prices in different areas.
        <br />
        <b> Security & Safety –</b>Check crime rates and property security
        features.
        <br />
        <b> Lease Agreement –</b>Read terms regarding deposits, maintenance, and
        lease duration.
      </p>
    ),
  },
];
function City4FrequentlyAskedQuestionsSection() {
  const itemListSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",

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

export default City4FrequentlyAskedQuestionsSection;
