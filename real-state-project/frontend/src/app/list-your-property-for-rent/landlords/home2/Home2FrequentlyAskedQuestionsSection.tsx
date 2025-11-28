import { Box, Container, Flex, Grid, Text } from "@mantine/core";
import React from "react";
import FrequentlyAskedQuestions from "../../../home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
import CustomButton from "@/components/customButton/CustomButton";
import Link from "next/link";
import JsonLdScript from "../../../JsonLdScript";
export const faqData = [
  {
    value: "How can I schedule a property viewing with a tenant?",
    description:
      "Once you list your property for rent, you can log in to the landlord CRM and schedule a meeting for property viewing. Our system allows you to pick a time slot, and the tenant will receive an automated WhatsApp notification with the meeting details.",
  },
  {
    value: "What is a dynamic rental contract, and how does it work?",
    description:
      "A dynamic rental contract is a digital agreement that landlords and tenants can customize based on specific rental terms. With PocketProperty, you can generate a legally binding contract tailored to your property details, rental price, and duration—all without the hassle of paperwork.",
  },
  {
    value: "Can I manage multiple property listings from one account?",
    description:
      "Yes! Our landlord CRM allows you to manage multiple property listings from a single dashboard. You can list your property online, track tenant inquiries, schedule viewings, and update rental details—all in one place.",
  },
  {
    value: "How does the tenant matching system work?",
    description:
      "Once you list your property, our smart matching system instantly connects you with verified tenants looking to rent a house or apartment online. When a match is found, you’ll receive an instant WhatsApp notification with tenant details, allowing you to schedule a viewing or finalize the rental process.",
  },
  {
    value: "What happens after a tenant agrees to rent my property?",
    description: (
      <p>
        After a tenant confirms their interest, you can proceed with: <br />
        1. ⁠Genetate a dynamic rental agreement via our platform <br />
        2. ⁠Finalise the contract signing process
      </p>
    ),
  },
];
function Home2FrequentlyAskedQuestionsSection() {
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

export default Home2FrequentlyAskedQuestionsSection;
