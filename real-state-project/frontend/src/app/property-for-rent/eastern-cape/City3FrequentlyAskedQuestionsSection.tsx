import React from "react";
import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
//import { faqData } from "@/app/landlord/home2/Home2FrequentlyAskedQuestionsSection";
import CustomButton from "@/components/customButton/CustomButton";
import { Box, Container, Flex, Text } from "@mantine/core";
import Link from "next/link";
import JsonLdScript from "../../JsonLdScript";

const faqData = [
  {
    value: "Where can I find affordable property for rent in Eastern Cape?",
    description:
      "Affordable property for rent in Eastern Cape can be found in Mthatha, Grahamstown (Makhanda), and certain areas of Gqeberha (Port Elizabeth). These locations offer budget-friendly flats and houses while still providing access to essential amenities and transport links.",
  },
  {
    value: "What are the best areas to rent in East London?",
    description: (
      <>
        East London offers a variety of rental options, including:
        <br />
        <b>Beacon Bay & Nahoon –</b> Ideal for families and professionals, close
        to schools and beaches.
        <br />
        <b>Quigney & Southernwood –</b> Great for students and young
        professionals looking for budget-friendly apartments.
        <br />
        <b>Vincent & Amalinda –</b> Secure neighborhoods with a mix of houses
        and flats for rent.
        <br />
      </>
    ),
  },
  {
    value:
      " How much does it cost to rent an apartment in East London, South Africa?",
    description: (
      <>
        <b>
          The cost of <b>apartments to rent in East London</b>, South Africa
          varies based on location and size:
        </b>
        <br />
        <b> 1-bedroom apartments:</b> ZAR 4,500 – ZAR 7,500 per month.
        <br />
        <b>2-bedroom apartments:</b> ZAR 6,500 – ZAR 10,500 per month.
        <br />
        <b> Luxury apartments & beachfront properties:</b> ZAR 12,000+ per
        month.
        <br />
      </>
    ),
  },

  {
    value: "What should I consider before renting a property in Eastern Cape?",
    description: (
      <>
        <b>Before renting, consider:</b>
        <br />
        <b> Location –</b>Choose an area that suits your lifestyle (coastal,
        city, or suburban).
        <br />
        <b> Budget – </b>Compare rental prices in different neighborhoods.
        <br />
        <b> Security–</b>CCheck for safe, well-maintained buildings and gated
        communities.
        <br />
        <b> Lease Terms –</b>Understand rental agreements, deposits, and
        maintenance responsibilities.
      </>
    ),
  },

  {
    value: "Is Jeffreys Bay a good place to rent a flat?",

    description: (
      <>
        Yes! <b>Flats to rent in Jeffreys Bay </b> are perfect for those who
        love coastal living. The area offers:
        <br />
        <b>I Affordable and luxury rental options.</b>
        <br />
        <b> A relaxed lifestyle with easy access to the beach.</b>
        <br />
        <b>
          A strong sense of community with a mix of locals and holiday visitors.
        </b>
        <br />
      </>
    ),
  },
];
function City3FrequentlyAskedQuestionsSection() {
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

export default City3FrequentlyAskedQuestionsSection;
