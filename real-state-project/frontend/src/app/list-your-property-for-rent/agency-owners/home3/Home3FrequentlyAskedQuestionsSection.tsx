import { Box, Container, Flex, Grid, Text } from "@mantine/core";
import React from "react";
import CustomButton from "@/components/customButton/CustomButton";
import FrequentlyAskedQuestions from "@/app/home/components/frequentlyAskedQuestions/FrequentlyAskedQuestions";
import Link from "next/link";
import JsonLdScript from "../../../JsonLdScript";

const faqData = [
  {
    value:
      "What are the benefits of using PocketProperty’s property management software for agencies?",
    description:
      "PocketProperty’s rental property management software helps agencies list multiple properties, automate tenant matching, schedule viewings, and track leads—all in one place. It streamlines property management and reduces vacancies efficiently.",
  },
  {
    value: "Can I manage multiple agents under my PocketProperty account?",
    description:
      "Yes! Our property management system supports multiple agents under a single agency account. You can assign properties, track agent performance, and manage inquiries efficiently.",
  },
  {
    value:
      "What makes PocketProperty different from other rental property listing websites for agents?",
    description:
      "Unlike traditional rental property listing websites for agents, PocketProperty offers an end-to-end online property rental management solution, including CRM, lead tracking, automated scheduling, and WhatsApp tenant notifications to speed up the rental process.",
  },
  {
    value: "How does automated tenant matching work in PocketProperty?",
    description:
      "Our property management software automatically matches tenants with suitable rental properties based on their preferences. Once a match is found, both the tenant and the agency receive instant notifications, making tenant acquisition faster and more efficient.",
  },
  {
    value: "Is the CRM included in the property management system?",
    description:
      "Yes! PocketProperty’s built-in CRM helps agencies track tenant inquiries, manage leads, schedule property viewings, and monitor engagement, all within one platform—no extra cost.",
  },
];
function Home3FrequentlyAskedQuestionsSection() {
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
                    Need help?
                  </span>
                  <p
                    style={{
                      fontSize: "18px ",
                      fontWeight: 600,
                      color: "#2C2C2C",
                      marginLeft: "3px",
                    }}
                  >
                    Talk to our Support Team!
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

export default Home3FrequentlyAskedQuestionsSection;
